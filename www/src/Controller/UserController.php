<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    const LENGTH_AUTH_TOKEN = 32;
    const MIN_LENGTH_USERNAME = 4;
    const MAX_LENGTH_USERNAME = 64;

    const MIN_LENGTH_PASSWORD = 4;
    const MAX_LENGTH_PASSWORD = 64;

    const MIN_LENGTH_NAME = 3;
    const MAX_LENGTH_NAME = 64;

    const MIN_LENGTH_SURNAME = 4;
    const MAX_LENGTH_SURNAME = 64;

    private function validateField(
        ?string $field,
        int $minLength,
        int $maxLength,
        string $fieldName,
        TranslatorInterface $translator
    ): ?array {
        if (empty($field)) {
            return [
                'success' => false,
                'data' => [],
                'error' => $translator->trans('validation.field_required', ['%field%' => $fieldName]),
            ];
        }

        if (mb_strlen($field) < $minLength) {
            return [
                'success' => false,
                'data' => [],
                'error' => $translator->trans('validation.field_min_length', [
                    '%field%' => $fieldName,
                    '%min%' => $minLength,
                ]),
            ];
        }

        if (mb_strlen($field) > $maxLength) {
            return [
                'success' => false,
                'data' => [],
                'error' => $translator->trans('validation.field_max_length', [
                    '%field%' => $fieldName,
                    '%max%' => $maxLength,
                ]),
            ];
        }

        return null;
    }

    #[Route('/users', name: 'app_my_user')]
    public function index(EntityManagerInterface $entityManager, AuthService $authService): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $user = $authService->getCurrentUser();

        return $this->render('user/usr_list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'user' => $user,
        ]);
    }

    #[Route('/user_info', name: 'user_info')]
    public function myUser(EntityManagerInterface $entityManager, AuthService $authService): Response
    {
        $user = $authService->getCurrentUser();

        return $this->render('user/usr_info.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    #[Route('/user/create', name: 'user_create')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $username = $request->get('username');
        $password = $request->get('password');
        $name = $request->get('name');
        $surname = $request->get('surname');

        $usernameValidationResult = $this->validateField(
            $username,
            self::MIN_LENGTH_USERNAME,
            self::MAX_LENGTH_USERNAME,
            $translator->trans('field.username'),
            $translator
        );
        if ($usernameValidationResult) {
            return $this->json($usernameValidationResult);
        }

        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.username_exists'),
            ]);
        }

        $passwordValidationResult = $this->validateField(
            $password,
            self::MIN_LENGTH_PASSWORD,
            self::MAX_LENGTH_PASSWORD,
            $translator->trans('field.password'),
            $translator
        );
        if ($passwordValidationResult) {
            return $this->json($passwordValidationResult);
        }

        $nameValidationResult = $this->validateField(
            $name,
            self::MIN_LENGTH_NAME,
            self::MAX_LENGTH_NAME,
            $translator->trans('field.first_name'),
            $translator
        );
        if ($nameValidationResult) {
            return $this->json($nameValidationResult);
        }

        $surnameValidationResult = $this->validateField(
            $surname,
            self::MIN_LENGTH_SURNAME,
            self::MAX_LENGTH_SURNAME,
            $translator->trans('field.last_name'),
            $translator
        );
        if ($surnameValidationResult) {
            return $this->json($surnameValidationResult);
        }

        try {
            $authToken = bin2hex(random_bytes(self::LENGTH_AUTH_TOKEN));
        } catch (\Exception $e) {
            error_log((string) $e);

            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.auth_token_create_failed'),
            ]);
        }

        $user = new User();
        $user->setUsername($username);
        $user->setPasswordHash(md5($password));
        $user->setName($name);
        $user->setSurname($surname);
        $user->setAuthToken($authToken);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['user_id' => $user->getId()]);
    }

    #[Route('/user/login', name: 'user_login')]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator,
        AuthService $authService
    ): Response {
        $username = $request->get('username');
        $password = $request->get('password');

        if (empty($username) || mb_strlen($username) < self::MIN_LENGTH_USERNAME || mb_strlen($username) > self::MAX_LENGTH_USERNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_username_field'),
            ]);
        }

        if (empty($password) || mb_strlen($password) < self::MIN_LENGTH_PASSWORD || mb_strlen($password) > self::MAX_LENGTH_PASSWORD) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_password_field'),
            ]);
        }

        if (!$authService->login($username, $password)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_credentials'),
            ]);
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);

        $response = $this->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user?->getId(),
                    'username' => $user?->getUsername(),
                    'name' => $user?->getName(),
                    'surname' => $user?->getSurname(),
                ],
            ],
        ]);
        if ($user) {
            $response->headers->setCookie($authService->createAuthCookie($user->getAuthToken()));
        }

        return $response;
    }

    #[Route('/user/logout', name: 'user_logout')]
    public function logout(Request $request, AuthService $authService, TranslatorInterface $translator): Response
    {
        $response = $this->json([
            'success' => true,
            'data' => [$translator->trans('auth.logout_success')]
        ]);
        $response->headers->setCookie($authService->createLogoutCookie());

        return $response;
    }

    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_my_user');
    }
}
