<?php

namespace App\EventSubscriber\Github;

use App\Entity\Github\User;
use App\Message\Github\UpdateUserRepoMessage;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class UserSubscriber
{
    public function __construct(private readonly MessageBusInterface $messageBus){}

    public function prePersist(User $user, LifecycleEventArgs $event): void
    {
        $this->messageBus->dispatch(new UpdateUserRepoMessage($user->getGithubUserId()));
    }
}
