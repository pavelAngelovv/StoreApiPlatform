<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a new user')
            ->addArgument('username', InputArgument::REQUIRED, 'User username')
            ->addArgument('password', InputArgument::REQUIRED, 'User password')
            ->addArgument('role', InputArgument::REQUIRED, 'User role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        $violations = $this->validator->validate([
            'username' => $username,
            'password' => $password,
            'role' => $role,
        ], new Assert\Collection([
            'username' => [new Assert\NotBlank()],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length(['min' => 16]),
                new Assert\Regex('/[A-Z]/', 'Password must contain at least one uppercase letter'),
                new Assert\Regex('/[a-z]/', 'Password must contain at least one lowercase letter'),
                new Assert\Regex('/\d/', 'Password must contain at least one digit'),
            ],
            'role' => [
                new Assert\NotBlank(),
                new Assert\Choice(
                    choices: ['ROLE_USER', 'ROLE_ADMIN'],
                    message: 'Invalid role. Allowed values are ROLE_USER or ROLE_ADMIN.'
                ),
            ],
        ]));

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $io->error($violation->getMessage());
            }
            return Command::FAILURE;
        }

        $user = new User();
        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setRoles([$role]);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User created successfully.');
        return Command::SUCCESS;
    }
}
