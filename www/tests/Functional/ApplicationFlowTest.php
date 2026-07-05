<?php

namespace App\Tests\Functional;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationFlowTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();

        $this->purgeDatabase();
    }

    public function testGuestCanReadFeedAndSwitchLocale(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Лента публикаций');
        self::assertSelectorTextContains('.auth-notice', 'Войдите в аккаунт');

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', '/locale/en');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Publication feed');
        self::assertSelectorTextContains('.auth-notice', 'Log in to publish posts');
        self::assertSame('EN', trim($crawler->filter('.locale-switch a.active')->text()));
    }

    public function testUserCanRegisterLoginAndPublishPost(): void
    {
        $this->registerUser('johnny', 'secret', 'John', 'Smith');
        $passwordHash = $this->getUserPasswordHash('johnny');

        self::assertNotSame(md5('secret'), $passwordHash);
        self::assertDoesNotMatchRegularExpression('/^[a-f0-9]{32}$/i', $passwordHash);

        $this->loginUser('johnny', 'secret');
        $this->createArticle('First public note');

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.user-pill', 'johnny');
        self::assertSelectorTextContains('.feed-text', 'First public note');
        self::assertSelectorTextContains('#addArticleButton', 'Новая запись');
    }

    public function testLegacyMd5PasswordIsRehashedAfterSuccessfulLogin(): void
    {
        $this->getDatabaseConnection()->insert('users', [
            'username' => 'legacy',
            'password_hash' => md5('secret'),
            'name' => 'Legacy',
            'surname' => 'User',
        ]);

        $this->loginUser('legacy', 'secret');
        $passwordHash = $this->getUserPasswordHash('legacy');

        self::assertNotSame(md5('secret'), $passwordHash);
        self::assertDoesNotMatchRegularExpression('/^[a-f0-9]{32}$/i', $passwordHash);
    }

    public function testAuthenticatedUserCanCommentOnPost(): void
    {
        $this->registerUser('author', 'secret', 'Post', 'Author');
        $this->loginUser('author', 'secret');
        $this->createArticle('Post for comments');

        $this->postWithCsrf('/comment/add', 'comment-add', [
            'article_id' => 1,
            'text' => 'Nice post',
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['comment_id' => 1]);

        $this->client->request('GET', '/comments/1');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.comments-toolbar h1', 'Комментарии');
        self::assertSelectorTextContains('.comment-item p', 'Nice post');
    }

    public function testAuthenticatedUserCanLogout(): void
    {
        $this->registerUser('logout', 'secret', 'Log', 'User');
        $this->loginUser('logout', 'secret');

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.user-pill', 'logout');

        $this->postWithCsrf('/user/logout', 'logout');

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['success' => true]);

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('.user-pill');
        self::assertSelectorTextContains('.auth-notice', 'Войдите в аккаунт');
    }

    public function testAccountPageShowsCurrentUser(): void
    {
        $this->registerUser('profile', 'secret', 'User', 'Profile');
        $this->loginUser('profile', 'secret');

        $this->client->request('GET', '/user_info');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.profile-heading h1', 'profile');
        self::assertSelectorExists('input[value="User"]');
        self::assertSelectorExists('input[value="Profile"]');
    }

    private function registerUser(string $username, string $password, string $name, string $surname): void
    {
        $this->postWithCsrf('/user/create', 'register', [
            'username' => $username,
            'password' => $password,
            'name' => $name,
            'surname' => $surname,
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['user_id' => 1]);
    }

    private function loginUser(string $username, string $password): void
    {
        $this->postWithCsrf('/user/login', 'login', [
            'username' => $username,
            'password' => $password,
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['success' => true]);
    }

    private function createArticle(string $text): void
    {
        $this->postWithCsrf('/article/add', 'article-add', [
            'text' => $text,
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['article_id' => 1]);
    }

    private function postWithCsrf(string $uri, string $tokenName, array $parameters = []): void
    {
        $parameters['_csrf_token'] = $this->fetchCsrfToken($tokenName);

        $this->client->request('POST', $uri, $parameters);
    }

    private function fetchCsrfToken(string $tokenName): string
    {
        $crawler = $this->client->request('GET', '/');
        $token = $crawler->filter('body')->attr('data-csrf-'.$tokenName);

        self::assertResponseIsSuccessful();
        self::assertIsString($token);
        self::assertNotSame('', $token);

        return $token;
    }

    private function purgeDatabase(): void
    {
        $this->getDatabaseConnection()->executeStatement('TRUNCATE comments, articles, users RESTART IDENTITY CASCADE');
    }

    private function getUserPasswordHash(string $username): string
    {
        $passwordHash = $this->getDatabaseConnection()->fetchOne(
            'SELECT password_hash FROM users WHERE username = :username',
            ['username' => $username]
        );

        self::assertIsString($passwordHash);

        return $passwordHash;
    }

    private function getDatabaseConnection(): Connection
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);

        return $connection;
    }

    private function assertJsonResponseContains(array $expected): void
    {
        $payload = json_decode($this->getClientResponseContent(), true, 512, JSON_THROW_ON_ERROR);

        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $payload);
            self::assertSame($value, $payload[$key]);
        }
    }

    private function getClientResponseContent(): string
    {
        $response = $this->client->getResponse();
        $content = $response->getContent();

        self::assertIsString($content);

        return $content;
    }
}
