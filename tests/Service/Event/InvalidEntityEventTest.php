<?php

namespace App\Tests\Service\Event;

use App\Service\Event\InvalidEntityEvent;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class InvalidEntityEventTest extends TestCase
{
    private StdClass $entity;
    private ConstraintViolationList $constraint;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entity = new stdClass();
        $this->constraint = new ConstraintViolationList([$this->createMock(ConstraintViolation::class)]);
    }

    /**
     * Checks that the entity is set properly.
     *
     * @return InvalidEntityEvent
     */
    public function testSetGetEntity(): InvalidEntityEvent
    {
        $event = new InvalidEntityEvent();
        $event->setEntity($this->entity);

        $this->assertEquals(spl_object_hash($this->entity), spl_object_hash($event->getEntity()));

        return $event;
    }

    /**
     * Checks that there are no constraints by default.
     *
     * @depends testSetGetEntity
     * @param InvalidEntityEvent $event
     * @return InvalidEntityEvent
     */
    public function testHasNotConstraintsByDefault(InvalidEntityEvent $event): InvalidEntityEvent
    {
        $event->setEntity($this->entity);

        $this->assertFalse($event->hasConstraints());

        return $event;
    }

    /**
     * Checks that the constraints are set correctly.
     *
     * @depends testHasNotConstraintsByDefault
     * @param InvalidEntityEvent $event
     * @return InvalidEntityEvent
     */
    public function testSetConstraints(InvalidEntityEvent $event): InvalidEntityEvent
    {
        $event->setConstraints($this->constraint);

        $this->assertTrue($event->hasConstraints());

        return $event;
    }

    /**
     * Checks out the constraints getter.
     *
     * @depends testSetConstraints
     * @param InvalidEntityEvent $event
     * @return void
     */
    public function setGetConstraint(InvalidEntityEvent $event): void
    {
        $this->assertEquals(spl_object_hash($this->constraint), spl_object_hash($event->getConstraints()));
    }
}
