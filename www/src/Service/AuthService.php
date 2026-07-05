<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    const AUTH_EXPIRE = 2419200;

    protected EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        private readonly RequestStack $requestStack,
        private readonly UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->entityManager = $entityManager;
    }

    public function createAuthCookie(string $cookieValue): Cookie
    {
        return Cookie::create('auth_cookie', $cookieValue, time() + self::AUTH_EXPIRE, '/', null, false, true, false, Cookie::SAMESITE_LAX);
    }

    public function createLogoutCookie(): Cookie
    {
        return Cookie::create('auth_cookie', '', time() - 1, '/', null, false, true, false, Cookie::SAMESITE_LAX);
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

        if (!$this->isPasswordValid($user, $password)) {
            return false;
        }

        $authCookie = $this->generateAuthCookie();
        $user->setAuthToken($authCookie);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return true;
    }

    public function getUserInfoByAuthToken(): ?User
    {
        $authCookie = $this->getAuthCookieValue();
        if ($authCookie) {
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
        $authCookie = $this->getAuthCookieValue();
        if (!$authCookie) {
            return null;
        }

        return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);
    }

    /**
     * @throws Exception
     */
    private function generateAuthCookie(): string
    {
        return bin2hex(random_bytes(32));
    }

    protected function verifyAuthCookie(): ?User
    {
        $authCookie = $this->getAuthCookieValue();
        if ($authCookie) {
            return $this->entityManager->getRepository(User::class)->findOneBy(['authToken' => $authCookie]);
        }

        return null;
    }

    private function getAuthCookieValue(): ?string
    {
        return $this->requestStack->getCurrentRequest()?->cookies->get('auth_cookie') ?? $_COOKIE['auth_cookie'] ?? null;
    }

    private function isPasswordValid(User $user, string $password): bool
    {
        if ($this->passwordHasher->isPasswordValid($user, $password)) {
            return true;
        }

        $passwordHash = $user->getPasswordHash();
        if (!$this->isLegacyMd5Hash($passwordHash) || !hash_equals($passwordHash, md5($password))) {
            return false;
        }

        $user->setPasswordHash($this->passwordHasher->hashPassword($user, $password));

        return true;
    }

    private function isLegacyMd5Hash(?string $passwordHash): bool
    {
        return is_string($passwordHash) && preg_match('/^[a-f0-9]{32}$/i', $passwordHash) === 1;
    }
}
