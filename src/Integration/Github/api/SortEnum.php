<?php

namespace App\Integration\Github\api;

enum SortEnum
{
    case full_name;
    case created;
    case updated;
    case pushed;
}
