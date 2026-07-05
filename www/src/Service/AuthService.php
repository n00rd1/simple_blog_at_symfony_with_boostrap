<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AuthService
{
    const AUTH_EXPIRE = 2419200;

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setAuthCookie(string $cookieValue): void
    {
        setcookie('auth_cookie', $cookieValue, time() + self::AUTH_EXPIRE, '/');
    }

    /**
     * @throws Exception
     */
    public function login(string $username, string $password): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
        if (!$user) {
            return false;
        }

        if ($user->getPasswordHash() !== md5($password)) {
            return false;
        }

        $authCookie = $this->generateAuthCookie();
        $user->setAuthToken($authCookie);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function logout(): void
    {
        setcookie('auth_cookie', '', time() - 1, '/');
    }

    public function getUserInfoByAuthToken(): ?User
    {
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);

            if (!$user) {
                return null;
            }

            return $user;
        }

        return null;
    }

    public function getCurrentUser(): ?User
    {
        if (empty($_COOKIE['auth_cookie'])) {
            return null;
        }
        return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $_COOKIE['auth_cookie']]);
    }

    /**
     * @throws Exception
     */
    private function generateAuthCookie(): string
    {
        $randomValue = bin2hex(random_bytes(32));
        $this->setAuthCookie($randomValue);

        return $randomValue;
    }

    protected function verifyAuthCookie(): ?User
    {
        if (isset($_COOKIE['auth_cookie'])) {
            $authCookie = $_COOKIE['auth_cookie'];

            return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);
        }

        return null;
    }
}
