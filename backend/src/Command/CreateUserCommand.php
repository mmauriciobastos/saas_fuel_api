<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user with email/password and attach to a company (by slug). Creates the company if missing.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password')
            ->addArgument('company', InputArgument::REQUIRED, 'Company slug (will be created if missing)')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Grant ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $plainPassword = (string) $input->getArgument('password');
        $companySlug = (string) $input->getArgument('company');
        $isAdmin = (bool) $input->getOption('admin');

        $companyRepo = $this->em->getRepository(Company::class);
        $company = $companyRepo->findOneBy(['slug' => $companySlug]);
        if (!$company) {
            $company = new Company();
            $company->setName(ucwords(str_replace(['-', '_'], ' ', $companySlug)) ?: $companySlug);
            $company->setSlug($companySlug);
            $this->em->persist($company);
            $output->writeln(sprintf('<info>Created company</info>: %s', $companySlug));
        }

        $userRepo = $this->em->getRepository(User::class);
        $existing = $userRepo->findOneBy(['email' => $email]);
        if ($existing) {
            $output->writeln('<error>User already exists.</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setCompany($company);
        if ($isAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln(sprintf('<info>User created:</info> %s (company: %s)%s', $email, $companySlug, $isAdmin ? ' [ROLE_ADMIN]' : ''));
        return Command::SUCCESS;
    }
}
