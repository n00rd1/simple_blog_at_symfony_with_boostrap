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

        $this->client->request('POST', '/user/login', [
            'username' => 'johnny',
            'password' => 'secret',
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['success' => true]);

        $this->client->request('POST', '/article/add', [
            'text' => 'First public note',
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['article_id' => 1]);

        $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.user-pill', 'johnny');
        self::assertSelectorTextContains('.feed-text', 'First public note');
        self::assertSelectorTextContains('#addArticleButton', 'Новая запись');
    }

    public function testAuthenticatedUserCanCommentOnPost(): void
    {
        $this->registerUser('author', 'secret', 'Post', 'Author');
        $this->loginUser('author', 'secret');
        $this->createArticle('Post for comments');

        $this->client->request('POST', '/comment/add', [
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
        $this->client->request('POST', '/user/create', [
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
        $this->client->request('POST', '/user/login', [
            'username' => $username,
            'password' => $password,
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['success' => true]);
    }

    private function createArticle(string $text): void
    {
        $this->client->request('POST', '/article/add', [
            'text' => $text,
        ]);

        self::assertResponseIsSuccessful();
        $this->assertJsonResponseContains(['article_id' => 1]);
    }

    private function purgeDatabase(): void
    {
        /** @var Connection $connection */
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement('TRUNCATE comments, articles, users RESTART IDENTITY CASCADE');
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
