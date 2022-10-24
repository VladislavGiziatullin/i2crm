<?php

namespace App\Integration\Github\api;

enum ResourcesEnum
{
    case core;
    case search;
    case graphql;
    case integration_manifest;
    case undefined; // for check rate limits
}
