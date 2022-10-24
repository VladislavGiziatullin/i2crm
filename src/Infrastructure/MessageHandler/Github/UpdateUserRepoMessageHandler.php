<?php

namespace App\Infrastructure\MessageHandler\Github;

use App\Infrastructure\Integration\Github\Exception\ApiLimitExceedException;
use App\Infrastructure\Integration\Github\Exception\BadRefreshTokenException;
use App\Infrastructure\Integration\Github\Exception\InternalServerErrorException;
use App\Infrastructure\Message\Github\UpdateUserRepoMessage;
use App\Service\Github\UserRepoService;
use App\Service\Github\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

final class UpdateUserRepoMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
        private readonly UserRepoService $userRepoService,
        private readonly UserService $userService,
    ){}

    public function __invoke(UpdateUserRepoMessage $message): void
    {
        $githubUserId = $message->getGithubUserId();
        $user = $this->userService->getByGithubUserId($githubUserId);
        if ($user === null) {
            throw new UnrecoverableMessageHandlingException("Github user {$githubUserId} not found");
        }

        try {
            $this->userRepoService->updateUserRepos($user);
        } catch (ApiLimitExceedException $exception) {
            $this->logger->notice($exception->getMessage());
            $this->messageBus->dispatch($message, [
                new DelayStamp($exception->resetIn)
            ]);
        } catch (InternalServerErrorException $exception) {
            throw new RecoverableMessageHandlingException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (BadRefreshTokenException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
