<?php

namespace App\Integration\Github\api;

enum UserRepoTypeEnum
{
    case all;
    case owner;
    case member;
}
