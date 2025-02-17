<?php

namespace App\Service\EntityFactory;

use App\Service\Event\InvalidEntityEvent;
use App\ValueObject\FileHeaders;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * This entity factory implementation proposes filling an entity with data from an array followed by validation and
 * triggering an invalid entity event on validation fail.
 *
 * This class is needed for additional entity validations.
 * This may include specific business rules or checks for unique keys.
 */
abstract class AbstractEntityFactory implements EntityFactoryInterface
{
    public function __construct(
        protected ValidatorInterface $validator,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * Method for creating a specific entity from a file row.
     *
     * @param mixed $row properties data.
     * @param FileHeaders $headers Keys from the file so you could refer to the array keys by understanding their meaning.
     */
    abstract protected function populate(array $row, FileHeaders $headers): object;

    /**
     * Method for creating and validating a specific entity.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return object|null
     */
    public function create(array $row, FileHeaders $headers): ?object
    {
        $entity = $this->populate($row, $headers);

        $constraints = $this->validator->validate($entity);

        if ($constraints->count()) {
            $event = new InvalidEntityEvent();
            $event->setEntity($entity);
            $event->setConstraints($constraints);

            $this->dispatcher->dispatch($event);
            return null;
        }

        return $entity;
    }
}
