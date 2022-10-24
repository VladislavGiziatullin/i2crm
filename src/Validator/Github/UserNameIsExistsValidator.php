<?php

namespace App\Validator\Github;

use App\Entity\Github\User;
use App\Integration\Github\Exception\NotFoundException;
use App\Repository\Github\APIRepository;
use Github\Exception\ApiLimitExceedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserNameIsExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly APIRepository $APIRepository) {}

    /**
     * @param User $value
     * @param UserNameIsExists $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        try {
            $username = $value->getUsername();
            $this->APIRepository->getUser($value->getAddedByUserId(), $username);
        } catch (ApiLimitExceedException $exception) {
            $this->context->buildViolation($exception->getMessage())->addViolation();
        } catch (NotFoundException $exception) {
            $this->context->buildViolation($constraint->message)
                ->atPath('username')
                ->setParameter('{{ value }}', $username)
                ->addViolation();
        }
    }
}
