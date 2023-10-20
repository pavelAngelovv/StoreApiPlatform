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

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    private UserPasswordHasherInterface $passwordHasher;
    private EntityManagerInterface $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        parent::__construct();

        $this->passwordHasher = $passwordHasher;
        $this->entityManager = $entityManager;
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

        if (!$this->isPasswordStrong($password)) {
            $io->error('Password does not meet the strength requirements.');
            return Command::FAILURE;
        }

        $user = new User();
        $user
            ->setUsername($username)
            ->setRoles([$role])
            ->setPlainPassword($password, $this->passwordHasher);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User created successfully.');
        return Command::SUCCESS;
    }

    private function isPasswordStrong(string $password): bool
    {
        $hasUpperCase = preg_match('/[A-Z]/', $password);
        $hasLowerCase = preg_match('/[a-z]/', $password);
        $hasDigit = preg_match('/\d/', $password);
        $hasSpecialChar = preg_match('/[^a-zA-Z\d]/', $password);

        return strlen($password) >= 16
            && $hasUpperCase
            && $hasLowerCase
            && $hasDigit
            && $hasSpecialChar;
    }
}
