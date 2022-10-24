<?php

namespace App\Infrastructure\Integration\Github\Exception;

use Throwable;

class UserNotFoundException extends Exception
{
    public function __construct(
        public readonly int $userId,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct(sprintf('User %d not found', $this->userId), $code, $previous);
    }
}
