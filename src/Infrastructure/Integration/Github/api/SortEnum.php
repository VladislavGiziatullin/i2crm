<?php

namespace App\Infrastructure\Integration\Github\api;

enum SortEnum
{
    case full_name;
    case created;
    case updated;
    case pushed;
}
