<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ensure-admin-user',
    description: 'Creates or updates the local admin user.',
)]
class EnsureAdminUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Admin username', 'admin')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Admin password', 'admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = (string) $input->getOption('username');
        $password = (string) $input->getOption('password');

        if ($username === '' || $password === '') {
            $io->error('Username and password must not be empty.');
            return Command::FAILURE;
        }

        $repository = $this->entityManager->getRepository(User::class);
        $user = $repository->findOneBy(['username' => $username]);
        $created = false;

        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($username);
            $user->setAuthToken(bin2hex(random_bytes(32)));
            $this->entityManager->persist($user);
            $created = true;
        }

        $user->setName('Admin');
        $user->setSurname('Admin');
        $user->setPasswordHash(md5($password));
        $this->entityManager->flush();

        $io->success(sprintf(
            '%s admin user "%s".',
            $created ? 'Created' : 'Updated',
            $username
        ));

        return Command::SUCCESS;
    }
}
