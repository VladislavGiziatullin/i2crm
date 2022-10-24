<?php

namespace App\Integration\Github\api;

use Psr\Http\Message\RequestInterface;

class RateLimit extends API
{
    public function get(): RequestInterface
    {
        return $this->createRequest('GET', $this->createUri('/rate_limit'));
    }

    protected function getResources(): ResourcesEnum
    {
        return ResourcesEnum::undefined;
    }
}
