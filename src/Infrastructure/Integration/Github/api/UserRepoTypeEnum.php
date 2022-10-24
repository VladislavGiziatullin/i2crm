<?php

namespace App\Infrastructure\Integration\Github\api;

enum UserRepoTypeEnum
{
    case all;
    case owner;
    case member;
}
