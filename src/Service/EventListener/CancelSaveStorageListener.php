<?php

namespace App\Service\EventListener;

use App\Service\Event\BeforePersistInStorageEvent;

/**
 * This is the save {@see BeforePersistInStorageEvent} listener that sets the cancel flag.
 *
 * It will always cancel the save, so this listener should only be attached when you definitely want to cancel the save.
 * If you're sure you want to keep saving for sure, in that case, make sure it's not attached to an event.
 */
class CancelSaveStorageListener
{
    /**
     * A method for canceling the saving of an entity.
     *
     * @param BeforePersistInStorageEvent $event
     * @return void
     */
    public function cancel(BeforePersistInStorageEvent $event): void
    {
        $event->cancelSave();
    }
}
