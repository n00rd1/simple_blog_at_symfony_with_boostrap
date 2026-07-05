<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260705123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove legacy auth token storage after switching to Symfony session authentication.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP CONSTRAINT users_auth_token_key');
        $this->addSql('ALTER TABLE users DROP COLUMN auth_token');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD auth_token VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE users SET auth_token = md5(random()::text || clock_timestamp()::text || id::text) WHERE auth_token IS NULL");
        $this->addSql('ALTER TABLE users ALTER auth_token SET NOT NULL');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT users_auth_token_key UNIQUE (auth_token)');
    }
}
