<?php

namespace App\Service\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that will be triggered before the entity is persisted in the
 * {@see DbEntityStorage} or {@see EntityStorageInterface}.
 *
 * Contains information about which entity is to be persisted and whether to cancel its saving.
 */
class BeforePersistInStorageEvent extends Event
{
    /**
     * An entity to be saved.
     *
     * @var object
     */
    protected object $entity;
    /**
     * Flag indicating whether the {@see $entity} saving should be canceled.
     *
     * @var bool
     */
    protected bool $isCancelled = false;

    public function __construct(object $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Provides entity to be saved.
     *
     * @return object
     */
    public function getEntity(): object
    {
        return $this->entity;
    }

    /**
     * Mark the cancelling flag as "true" (do not save the entity).
     *
     * @return void
     */
    public function cancelSave(): void
    {
        $this->isCancelled = true;
    }

    /**
     * Mark the cancelling flag as "false" (save the entity).
     *
     * @return void
     */
    public function doNotCancel(): void
    {
        $this->isCancelled = false;
    }

    /**
     * Getter for the cancelling flag.
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }
}
