<?php

namespace App\Infrastructure\Command;

use App\Infrastructure\Message\Github\UpdateUserRepoMessage;
use App\Service\Github\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:update-users-repos',
    description: 'Add a short description for your command',
)]
class GithubUpdateUsersReposCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly UserService $userService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->userService->getAll() as $user) {
            $this->messageBus->dispatch(new UpdateUserRepoMessage($user->getGithubUserId()));
        }
        $io = new SymfonyStyle($input, $output);

        $io->success('Created tasks for update github users repos');

        return Command::SUCCESS;
    }
}
