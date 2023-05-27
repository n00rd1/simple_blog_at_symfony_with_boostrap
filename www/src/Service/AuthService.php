<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AuthService
{
    const AUTH_EXPIRE = 2419200; // Время жизни печеньки (1 месяц)

    protected EntityManagerInterface $entityManager;

    /** Конструтор
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** Установка аутентификационной куки
     * @param string $cookieValue
     * @return void
     */
    public function setAuthCookie(string $cookieValue): void
    {
        // Устанавливаем печеньку с именем "auth_cookie" и значением, переданным в функцию
        // Время жизни куки составляет 1 месяц (значение AUTH_EXPIRE)
        setcookie('auth_cookie', $cookieValue, time() + self::AUTH_EXPIRE, '/');
    }

    /** Вход в систему и авторизация в личном кабинете
     * @param string $username
     * @param string $password
     * @return bool
     * @throws Exception
     */
    public function login(string $username, string $password): bool
    {
        // Ищем пользователя по имени пользователя
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            // Если пользователь не найден, возвращаем false
            return false;
        }

        // Проверяем, совпадает ли предоставленный пароль с хешем пароля пользователя
        if ($user->getPasswordHash() !== md5($password)) {
            // Если пароли не совпадают, возвращаем false
            return false;
        }

        // Генерируем новую аутентификационную печеньку
        $authCookie = $this->generateAuthCookie();

        // Сохраняем аутентификационную куку в базе данных
        $user->setAuthToken($authCookie);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    /** Выход из системы
     * @return void
     */
    public function logout(): void
    {
        // Удаляем аутентификационную печеньку
        setcookie('auth_cookie', '', time() - 1, '/');
    }

    /** Получение информации о пользователе по аутентификационному токену
     *
     * @return User|null
     */
    public function getUserInfoByAuthToken(): ?User
    {
        // Проверяем, установлена ли аутентификационная куки
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];

            // Ищем пользователя по аутентификационному токену
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);

            if (!$user) {
                // Если аутентификационный токен не найден, возвращаем null
                return null;
            }

            // Возвращаем информацию о пользователе
            return $user;
        }

        // Если аутентификационная куки не установлена, возвращаем null
        return null;
    }

    /** Получить текущего авторизованного пользователя
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        if (empty($_COOKIE['auth_cookie'])) {
            return null;
        }
        return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $_COOKIE['auth_cookie']]);
    }


    /** Генерация случайного значения для куки
     * @return string
     * @throws Exception
     */
    private function generateAuthCookie(): string
    {
        // Генерируем случайное значение для куки
        $randomValue = bin2hex(random_bytes(32));

        // Устанавливаем куку с использованием сгенерированного значения
        $this->setAuthCookie($randomValue);

        // Возвращаем случайное значение для дальнейшего использования
        return $randomValue;
    }


    /** Проверка аутентификационной куки
     * @return User|null
     */
    protected function verifyAuthCookie(): ?User
    {
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];

            // Ищем пользователя по аутентификационному токену
            return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);
        }

        // Если куки нет, возвращаем null
        return null;
    }
}