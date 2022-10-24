<?php

namespace App\Infrastructure\Integration\Github\Exception;

use Exception;
use Throwable;

class ApiLimitExceedException extends Exception implements GithubExceptionInterface
{
    public function __construct(
        public readonly string $resource,
        public readonly int $limit,
        public readonly int $resetIn,
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'You have reached GitHub hourly limit for resource "%s"! Actual limit is: %d. Next window after %d seconds',
                $this->resource,
                $this->limit,
                $this->resetIn
            ),
            $code,
            $previous
        );
    }
}
