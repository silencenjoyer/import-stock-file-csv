<?php

namespace App\Tests\Service\Event;

use App\Service\Event\BeforePersistInStorageEvent;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforePersistInStorageEventTest extends TestCase
{
    private StdClass $entity;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new stdClass();
    }

    /**
     * Checks that the getter returns the current entity.
     *
     * @return BeforePersistInStorageEvent
     */
    public function testGetEntity(): BeforePersistInStorageEvent
    {
        $event = new BeforePersistInStorageEvent($this->entity);
        $this->assertEquals(spl_object_hash($this->entity), spl_object_hash($event->getEntity()));

        return $event;
    }

    /**
     * Checks that the default cancellation status is false.
     *
     * @depends testGetEntity
     * @param BeforePersistInStorageEvent $event
     * @return BeforePersistInStorageEvent
     */
    public function testDefaultCancellationValue(BeforePersistInStorageEvent $event): BeforePersistInStorageEvent
    {
        $this->assertFalse($event->isCancelled());

        return $event;
    }

    /**
     * Checks that the undo call installs the changes
     *
     * @depends testDefaultCancellationValue
     * @param BeforePersistInStorageEvent $event
     * @return BeforePersistInStorageEvent
     */
    public function testCancel(BeforePersistInStorageEvent $event): BeforePersistInStorageEvent
    {
        $event->cancelSave();

        $this->assertTrue($event->isCancelled());

        return $event;
    }

    /**
     * @depends testCancel
     * @param BeforePersistInStorageEvent $event
     * @return void
     */
    public function testDoNotCancel(BeforePersistInStorageEvent $event): void
    {
        $event->doNotCancel();

        $this->assertFalse($event->isCancelled());
    }
}
