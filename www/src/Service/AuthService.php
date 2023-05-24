<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AuthService
{
    const AUTH_EXPIRE = 2419200; // 1 месяц

    protected EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Установка аутентификационной куки
    public function setAuthCookie(string $cookieValue) {
        // Устанавливаем печеньку с именем "auth_cookie" и значением, переданным в функцию
        // Время жизни куки составляет 1 месяц (значение AUTH_EXPIRE)
        setcookie('auth_cookie', $cookieValue, time() + self::AUTH_EXPIRE, '/');


    }

    // Генерация случайного значения для куки
    private function generateAuthCookie() {
        $randomValue = bin2hex(random_bytes(32));       // Генерируем случайное значение для куки
        $this->setAuthCookie($randomValue);                 // Устанавливаем куку с использованием сгенерированного значения
        return $randomValue;                                // Возвращаем случайное значение для дальнейшего использования
    }

    // Проверка аутентификационной куки
    private function verifyAuthCookie() {
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];

            // Ищем пользователя по аутентификационному токену
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);
            return $user;
        }
        return null;                                        // Если куки нет, возвращаем null
    }

    // Функционал входа в систему
    public function login(string $username, string $password): bool {
        // Ищем пользователя по имени пользователя
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return false;                                   // Если пользователь не найден, возвращаем false
        }

        // Проверяем, совпадает ли предоставленный пароль с хешем пароля пользователя
        if ($user->getPasswordHash() !== md5($password)) {
            return false;                                   // Если пароли не совпадают, возвращаем false
        }

        $authCookie = $this->generateAuthCookie();          // Генерируем новую аутентификационную печеньку
        $user->setAuthToken($authCookie);                   // Сохраняем аутентификационную куку в базе данных

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    // Функционал выхода из системы
    public function logout(): void {
        setcookie('auth_cookie', '', time() - 1, '/');      // Удаляем аутентификационную печеньку
    }

    // Получение информации о пользователе по аутентификационному токену
    public function getUserInfoByAuthToken() {
        // Проверяем, установлена ли аутентификационная куки
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];

            // Ищем пользователя по аутентификационному токену
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);

            if (!$user) {
                return null;                                // Если аутентификационный токен не найден, возвращаем null
            }

            return $user;                                   // Возвращаем информацию о пользователе

        }

        return null;                                        // Если аутентификационная куки не установлена, возвращаем null
    }
}