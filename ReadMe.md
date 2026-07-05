# n00rd1fy

[![CI/CD](https://github.com/n00rd1/simple_blog_at_symfony_with_boostrap/actions/workflows/ci-cd.yml/badge.svg)](https://github.com/n00rd1/simple_blog_at_symfony_with_boostrap/actions/workflows/ci-cd.yml)

n00rd1fy is a small Twitter-like Symfony application for publishing short posts, commenting, and working with simple user accounts. The project is built for learning Symfony, Doctrine, Twig, Bootstrap, Docker, and basic delivery automation.

## Stack

- PHP 8.3
- Symfony 6.4
- Symfony Security
- Doctrine ORM and Migrations
- PostgreSQL 18
- Nginx
- Bootstrap 5
- jQuery
- Docker Compose

## Features

- User registration and login
- Session-based authentication with CSRF-protected POST actions
- Short post publishing for authenticated users
- Comments for authenticated users
- Public feed for guests
- User list and profile page
- Russian and English interface
- Local admin bootstrap command
- GitHub Actions CI/CD workflow

## Local Setup

Clone the repository and start containers:

```bash
git clone https://github.com/n00rd1/simple_blog_at_symfony_with_boostrap.git
cd simple_blog_at_symfony_with_boostrap
docker compose up -d --build
```

Install PHP dependencies if needed:

```bash
docker compose exec php composer install
```

Run database migrations:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate
```

Create or reset the local admin user:

```bash
docker compose exec php php bin/console app:ensure-admin-user --username=admin --password=admin
```

Open the app:

```text
http://localhost:8080
```

Default local login:

```text
username: admin
password: admin
```

## Useful Commands

```bash
# Validate Docker Compose
docker compose config --quiet

# Check routes
docker compose exec php php bin/console debug:router

# Check migrations
docker compose exec php php bin/console doctrine:migrations:status

# Lint Twig templates
docker compose exec php php bin/console lint:twig templates

# Lint container wiring
docker compose exec php php bin/console lint:container

# Run tests
docker compose exec php php bin/phpunit
```

## CI/CD

GitHub Actions workflow: `.github/workflows/ci-cd.yml`

The workflow runs on pushes and pull requests to `main` and `master`.

CI checks:

- Composer metadata validation
- Composer install
- PHP syntax checks
- PostgreSQL service startup
- Doctrine database creation and migrations
- Admin user bootstrap command
- Symfony YAML, Twig, and container linting
- PHPUnit

Delivery automation:

- Docker Compose validation
- Docker image build for `php` and `postgresql`
- PHP runtime image publication to GitHub Container Registry on push:

```text
ghcr.io/n00rd1/n00rd1fy-php:latest
ghcr.io/n00rd1/n00rd1fy-php:<commit-sha>
```

Server deployment is intentionally not hard-coded. Add a deploy job when the production host, deployment path, and required GitHub secrets are known.

## Current Technical Debt

- Add browser-level coverage for the JavaScript registration/login flows and toast notifications.
- Add moderation and rate limiting around posts, likes, and comments before opening public write access.
- Add persistent user roles and moderation tools instead of deriving admin access from the local bootstrap account.

## Links

- [Symfony](https://symfony.com/)
- [Bootstrap](https://getbootstrap.com/)
- [Twig](https://twig.symfony.com/)
- [Doctrine](https://www.doctrine-project.org/)

## Contact

- Email: [mukhamedshin14@mail.ru](mailto:mukhamedshin14@mail.ru)
- GitHub: [@n00rd1](https://github.com/n00rd1)
- LinkedIn: [@n00rd1](https://www.linkedin.com/in/n00rd1/)
- Telegram: [@n00rd1](https://t.me/n00rd1)
