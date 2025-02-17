<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniquePersistedEntityValidator extends ConstraintValidator
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * {@inheritDoc}
     *
     * This implementation checks if the unique field value is in the buffer to be inserted.
     *
     * @param mixed $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniquePersistedEntity) {
            throw new UnexpectedTypeException($constraint, UniquePersistedEntity::class);
        }

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->em->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            if (!($entity instanceof $value)) {
                continue;
            }

            $hasError = false;
            foreach ($constraint->fields as $field) {
                if ($propertyAccessor->getValue($entity, $field) === $propertyAccessor->getValue($value, $field)) {
                    $hasError = true;
                    $this->context->buildViolation($constraint->message)->addViolation();
                }
            }

            if ($hasError) {
                return;
            }
        }
    }
}
