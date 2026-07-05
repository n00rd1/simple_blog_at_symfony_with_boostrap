<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
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
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/usr_list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/user_info', name: 'user_info')]
    public function myUser(): Response
    {
        return $this->render('user/usr_info.html.twig', [
            'controller_name' => 'UserController',
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/user/create', name: 'user_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        TranslatorInterface $translator
    ): Response {
        if (!$this->isCsrfTokenValid('register', (string) $request->request->get('_csrf_token'))) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_csrf'),
            ]);
        }

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

        $user = new User();
        $user->setUsername($username);
        $user->setPasswordHash($passwordHasher->hashPassword($user, $password));
        $user->setName($name);
        $user->setSurname($surname);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['user_id' => $user->getId()]);
    }

    #[Route('/user/login', name: 'user_login', methods: ['POST'])]
    public function login(TranslatorInterface $translator): Response
    {
        return $this->json([
            'success' => false,
            'data' => [],
            'error' => $translator->trans('error.invalid_credentials'),
        ]);
    }

    #[Route('/user/logout', name: 'user_logout', methods: ['POST'])]
    public function logout(Request $request, Security $security, TranslatorInterface $translator): Response
    {
        if (!$this->isCsrfTokenValid('logout', (string) $request->request->get('_csrf_token'))) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_csrf'),
            ]);
        }

        if ($security->getUser()) {
            $security->logout(false);
        }

        if ($request->hasSession()) {
            $request->getSession()->invalidate();
        }

        return $this->json([
            'success' => true,
            'data' => [$translator->trans('auth.logout_success')],
        ]);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['POST'])]
    public function delete(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException($translator->trans('error.user_not_authorized'));
        }

        if (!$this->isCsrfTokenValid('user_delete_'.$user->getId(), (string) $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException($translator->trans('error.invalid_csrf'));
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_my_user');
    }
}
