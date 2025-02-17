<?php

namespace App\Service\Event;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * An event containing information about an entity that has not been validated.
 *
 * Contains the entity itself and validation errors.
 */
class InvalidEntityEvent extends Event
{
    /**
     * Invalid data.
     *
     * @var array|object
     */
    protected array|object $entity;
    /**
     * Restriction that did not pass the data.
     *
     * @var ConstraintViolationListInterface
     */
    protected ConstraintViolationListInterface $constraints;

    /**
     * Setter for the {@see $entity} property.
     *
     * @param array|object $entity
     */
    public function setEntity(object|array $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * Getter for the {@see $entity} property.
     *
     * @return array|object
     */
    public function getEntity(): object|array
    {
        return $this->entity;
    }

    /**
     * Getter for the {@see $constraints} property.
     *
     * @return ConstraintViolationListInterface
     */
    public function getConstraints(): ConstraintViolationListInterface
    {
        return $this->constraints;
    }

    /**
     * Setter for the {@see $constraints} property.
     *
     * @param ConstraintViolationListInterface $constraints
     */
    public function setConstraints(ConstraintViolationListInterface $constraints): void
    {
        $this->constraints = $constraints;
    }

    /**
     * An indication of whether the event contains validation errors.
     *
     * @return bool
     */
    public function hasConstraints(): bool
    {
        return isset($this->constraints) && $this->constraints->count() > 0;
    }
}