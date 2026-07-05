<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'user_login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $username = (string) $request->request->get('username', '');
        $password = (string) $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($username),
            new CustomCredentials(fn (string $plainPassword, UserInterface $user): bool => $this->isPasswordValid($user, $plainPassword), $password),
            [new CsrfTokenBadge('login', is_string($csrfToken) ? $csrfToken : null)]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return $this->errorResponse('error.invalid_credentials');
        }

        return new JsonResponse([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'name' => $user->getName(),
                    'surname' => $user->getSurname(),
                ],
            ],
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($exception instanceof InvalidCsrfTokenException) {
            return $this->errorResponse('error.invalid_csrf');
        }

        return $this->errorResponse('error.invalid_credentials');
    }

    private function isPasswordValid(UserInterface $user, string $password): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->passwordHasher->isPasswordValid($user, $password)) {
            return true;
        }

        $passwordHash = $user->getPasswordHash();
        if (!$this->isLegacyMd5Hash($passwordHash) || !hash_equals($passwordHash, md5($password))) {
            return false;
        }

        $user->setPasswordHash($this->passwordHasher->hashPassword($user, $password));
        $this->entityManager->flush();

        return true;
    }

    private function isLegacyMd5Hash(?string $passwordHash): bool
    {
        return is_string($passwordHash) && preg_match('/^[a-f0-9]{32}$/i', $passwordHash) === 1;
    }

    private function errorResponse(string $translationKey): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => [],
            'error' => $this->translator->trans($translationKey),
        ]);
    }
}
