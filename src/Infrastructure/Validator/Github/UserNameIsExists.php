<?php

namespace App\Infrastructure\Validator\Github;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class UserNameIsExists extends Constraint
{
    public string $message = 'The username "{{ value }}" not found in github.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
