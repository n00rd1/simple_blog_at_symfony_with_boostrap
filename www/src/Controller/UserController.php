<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use phpDocumentor\Reflection\DocBlock\Tags\Uses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

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

    private function validateField($field, $minLength, $maxLength, $fieldName) {
        if (empty($field)) {                            // Проверка на пустоту поля
            return [
                'success' => false,
                'data' => [],
                'error' => "Не заполнено поле с $fieldName",
            ];
        }

        if (mb_strlen($field) < $minLength) {           // Проверка минимальной длины
            return [
                'success' => false,
                'data' => [],
                'error' => "Поле с $fieldName должно быть не менее $minLength символов",
            ];
        }

        if (mb_strlen($field) > $maxLength) {           // Проверка максимальной длины
            return [
                'success' => false,
                'data' => [],
                'error' => "Поле с $fieldName должно быть не более $maxLength символов",
            ];
        }

        return null;                                    // Если все проверки пройдены, возвращаем null
    }

    #[Route('/user', name: 'app_user')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('user/usr_list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $users,
        ]);
    }

    #[Route('/user/create', name: 'user_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $name = $request->get('name');
        $surname = $request->get('surname');

        // Валидация логина
        $usernameValidationResult = $this->validateField($username, self::MIN_LENGTH_USERNAME, self::MAX_LENGTH_USERNAME, 'логином');
        if ($usernameValidationResult) {
            return $this->json($usernameValidationResult);
        }

        // Проверка уникальности имени пользователя
        $existingUser = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUser) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Пользователь с таким логином уже существует',
            ]);
        }

        // Валидация пароля
        $passwordValidationResult = $this->validateField($password, self::MIN_LENGTH_PASSWORD, self::MAX_LENGTH_PASSWORD, 'паролем');
        if ($passwordValidationResult) {
            return $this->json($passwordValidationResult);
        }

        // Валидация имени пользователя
        $nameValidationResult = $this->validateField($name, self::MIN_LENGTH_NAME, self::MAX_LENGTH_NAME, 'именем');
        if ($nameValidationResult) {
            return $this->json($nameValidationResult);
        }

        // Валидация фамилии пользователя
        $surnameValidationResult = $this->validateField($surname, self::MIN_LENGTH_SURNAME, self::MAX_LENGTH_SURNAME, 'фамилией');
        if ($surnameValidationResult) {
            return $this->json($surnameValidationResult);
        }

        // Перевод пароля в необходимый формат для дальнейшего сравнения
        $passwordHash = md5($password);

        // Генерация токена для аудефикации
        try {
            $authToken = bin2hex(random_bytes(self::LENGTH_AUTH_TOKEN));
        } catch (\Exception $e) {
            error_log($e);                          // логируем ошибку
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Произошла ошибка при создании токена аутентификации',
            ]);
        }


        // Создание нового объекта (сущности) пользователя и заполненине всех его полей
        $user = new User();
        $user->setUsername($username);
        $user->setPasswordHash($passwordHash);;
        $user->setName($name);
        $user->setSurname($surname);
        $user->setAuthToken($authToken);

        // Отправка заполненного пользователя
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['user_id' => $user->getId()]);
    }

    #[Route('/user/login', name: 'user_login')]
    public function login(Request $request, EntityManagerInterface $entityManager): Response
    {
        $authService = new AuthService($entityManager);
        $username = $request->get('username');
        $password = $request->get('password');

        // Валидация имени пользователя
        if (empty($username) || mb_strlen($username) < self::MIN_LENGTH_USERNAME || mb_strlen($username) > self::MAX_LENGTH_USERNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Не верно заполнено поле с логином',
            ]);
        }

        // Валидация пароля
        if (empty($password) || mb_strlen($password) < self::MIN_LENGTH_PASSWORD || mb_strlen($password) > self::MAX_LENGTH_PASSWORD) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с паролем не должно быть пустым',
            ]);
        }

        // Проверяем вход пользователя с помощью AuthService
        if (!$authService->login($username, $password)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Неверный логин или пароль',
            ]);
        }

        // Если вход прошел успешно, получаем информацию о пользователе
        $user = $authService->getUserInfoByAuthToken();

        return $this->json([
            'success' => true,
            'data' => ['user' => $user],  // возвращаем информацию о пользователе
        ]);
    }

    #[Route('/user/{id}/delete', name: 'user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_user');
    }
}

/*
        if (empty($username)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Не заполнено поле с логином',
            ]);
        }

        if (mb_strlen($username) < self::MIN_LENGTH_USERNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с логином должно быть не менее ' . self::MIN_LENGTH_USERNAME . ' символов',
            ]);
        }

        if (mb_strlen($username) > self::MAX_LENGTH_USERNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с логином должно быть не более ' . self::MAX_LENGTH_USERNAME . ' символов',
            ]);
        }


        if (empty($password)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с паролем не должно быть пустым',
            ]);
        }

        if (mb_strlen($password) < self::MIN_LENGTH_PASSWORD) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с паролем должно быть не менее ' . self::MIN_LENGTH_PASSWORD . ' символов',
            ]);
        }

        if (mb_strlen($password) > self::MAX_LENGTH_PASSWORD) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с паролем должно быть не более ' . self::MAX_LENGTH_PASSWORD . ' символов',
            ]);
        }


        if (empty($name)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Не заполнено поле с именем',
            ]);
        }

        if (mb_strlen($name) < self::MIN_LENGTH_NAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с именем должно быть не менее ' . self::MIN_LENGTH_NAME . ' символов',
            ]);
        }

        if (mb_strlen($name) > self::MAX_LENGTH_NAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с именем должно быть не более ' . self::MAX_LENGTH_NAME . ' символов',
            ]);
        }


        if (empty($surname)) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Не заполнено поле с фамилией',
            ]);
        }

        if (mb_strlen($surname) < self::MIN_LENGTH_SURNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с фамилией должно быть не менее ' . self::MIN_LENGTH_SURNAME . ' символов',
            ]);
        }

        if (mb_strlen($surname) > self::MAX_LENGTH_SURNAME) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => 'Поле с фамилией должно быть не более ' . self::MAX_LENGTH_SURNAME . ' символов',
            ]);
        }
 */