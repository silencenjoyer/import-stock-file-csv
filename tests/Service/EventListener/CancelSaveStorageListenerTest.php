<?php

namespace App\Tests\Service\EventListener;

use App\Service\Event\BeforePersistInStorageEvent;
use App\Service\EventListener\CancelSaveStorageListener;
use PHPUnit\Framework\TestCase;
use stdClass;

class CancelSaveStorageListenerTest extends TestCase
{
    private CancelSaveStorageListener $listener;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->listener = new CancelSaveStorageListener();
    }

    /**
     * Checks if the event saving is canceled correctly.
     *
     * @return void
     */
    public function testCancel(): void
    {
        $event = new BeforePersistInStorageEvent(new StdClass());

        $this->listener->cancel($event);

        $this->assertTrue($event->isCancelled());
    }
}
