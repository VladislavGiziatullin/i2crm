<?php

namespace App\Validator\Github;

use Github\Client;
use Github\Exception\ApiLimitExceedException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UserNameIsExistsValidator extends ConstraintValidator
{
    public function __construct(private readonly Client $client) {}

    /**
     * @param UserNameIsExists $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        try {
            $this->client->user()->show($value);
        } catch (ApiLimitExceedException $exception) {
            $this->context->buildViolation($exception->getMessage())->addViolation();
        } catch (ClientExceptionInterface $exception) {
            if ($exception->getCode() === Response::HTTP_NOT_FOUND) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $value)
                    ->addViolation();
            } else {
                throw $exception;
            }

            // todo need handle another exception
        }
    }
}
