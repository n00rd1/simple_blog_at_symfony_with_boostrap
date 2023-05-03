<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503153116 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update some PostgreSQL code to create a database';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE users (  
                                id serial8 PRIMARY KEY,
                                username varchar(255) NOT NULL,
                                password_hash varchar(255) NOT NULL,
                                name varchar(64) NOT NULL,
                                surname varchar(64) NOT NULL,
                                auth_token varchar(255) NOT NULL,
                                UNIQUE (username),
                                UNIQUE (auth_token)
                            )
        ');

        $this->addSql('COMMENT ON TABLE users IS \'Таблица с параметрами пользователя (пользователей)\'');
        $this->addSql('COMMENT ON COLUMN users.id IS \'Уникальный идентификатор пользователя\'');
        $this->addSql('COMMENT ON COLUMN users.username IS \'Уникальное имя пользователя\'');
        $this->addSql('COMMENT ON COLUMN users.password_hash IS \'Зашифрованный пароль пользователя\'');
        $this->addSql('COMMENT ON COLUMN users.name IS \'Имя пользователя\'');
        $this->addSql('COMMENT ON COLUMN users.surname IS \'Дополнительное имя (фамилия) пользователя\'');
        $this->addSql('COMMENT ON COLUMN users.auth_token IS \'Токен пользователя для входа в систему (из куки-файла)\'');

        $this->addSql('CREATE TABLE articles (
                                id serial8 PRIMARY KEY,
                                author_id integer NOT NULL REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
                                text text NOT NULL,
                                created_at timestamp DEFAULT now()
                            )
        ');
        $this->addSql('COMMENT ON TABLE articles IS \'Таблица со статьями\'');
        $this->addSql('COMMENT ON COLUMN articles.id IS \'Уникальный идентификатор статьи\'');
        $this->addSql('COMMENT ON COLUMN articles.author_id IS \'Ссылка на автора статьи\'');
        $this->addSql('COMMENT ON COLUMN articles.text IS \'Текст пользователя\'');
        $this->addSql('COMMENT ON COLUMN articles.created_at IS \'Время написания статьи\'');

        $this->addSql('CREATE TABLE comments (
                                id serial8 PRIMARY KEY,
                                author_id integer NOT NULL REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE,
                                article_id integer NOT NULL REFERENCES articles (id) ON DELETE RESTRICT ON UPDATE CASCADE,
                                comment text NOT NULL,
                                created_at timestamp DEFAULT now()
                            )
        ');
                            
        $this->addSql('COMMENT ON TABLE comments IS \'Таблица с комментариями\'');
        $this->addSql('COMMENT ON COLUMN comments.id IS \'Уникальный идентификатор статьи\'');
        $this->addSql('COMMENT ON COLUMN comments.author_id IS \'Ссылка на автора комментария\'');
        $this->addSql('COMMENT ON COLUMN comments.author_id IS \'Ссылка на комментируему статью\'');
        $this->addSql('COMMENT ON COLUMN comments.comment IS \'Текст комментария\'');
        $this->addSql('COMMENT ON COLUMN comments.created_at IS \'Время написания комментария\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE users, articles, comments CASCADE');
    }
}
