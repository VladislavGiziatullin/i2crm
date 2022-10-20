<?php

namespace App\Validator\Github;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class UserNameIsExists extends Constraint
{
    public string $message = 'The username "{{ value }}" not found in github.';
}
