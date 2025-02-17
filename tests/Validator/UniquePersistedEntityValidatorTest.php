<?php

namespace App\Tests\Validator;

use App\Validator\UniquePersistedEntity;
use App\Validator\UniquePersistedEntityValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniquePersistedEntityValidatorTest extends TestCase
{
    private UniquePersistedEntityValidator $assert;
    private UniquePersistedEntity $entityAttribute;
    private EntityManagerInterface $em;
    private UnitOfWork $work;
    private ExecutionContextInterface $context;
    private ConstraintViolationBuilderInterface $violation;

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->context = $this->createMock(ExecutionContextInterface::class);

        $this->work = $this->createMock(UnitOfWork::class);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em->expects($this->once())->method('getUnitOfWork')->willReturn($this->work);

        $this->assert = new UniquePersistedEntityValidator($this->em);

        $this->entityAttribute = new UniquePersistedEntity(['name']);

        $this->violation = $this->createMock(ConstraintViolationBuilderInterface::class);
    }

    /**
     * Creates test entity.
     *
     * @param string $name
     * @return object
     */
    protected function createObject(string $name): object
    {
        $object = new class() {
            private ?string $name = null;

            public function setName(string $name): void
            {
                $this->name = $name;
            }

            public function getName(): ?string
            {
                return $this->name;
            }
        };

        $object->setName($name);
        return $object;
    }

    /**
     * Checking that the validator will not miss a duplicate that has been persisted.
     *
     * @return void
     */
    public function testDetectingDuplicate(): void
    {
        $this->violation->expects($this->once())
            ->method('addViolation');

        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->entityAttribute->message)
            ->willReturn($this->violation);

        $this->work->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([
                $this->createObject('John'),
                $this->createObject('Henry'),
                $this->createObject('Arthur'),
                $this->createObject('Barnaby'),
            ]);

        $this->assert->initialize($this->context);
        $this->assert->validate($this->createObject('John'), $this->entityAttribute);
    }

    /**
     * Checking that the validator will pass without a duplicate.
     *
     * @return void
     */
    public function testPassWithoutDuplicates(): void
    {
        $this->violation->expects($this->never())
            ->method('addViolation');

        $this->context->expects($this->never())
            ->method('buildViolation')
            ->with($this->entityAttribute->message)
            ->willReturn($this->violation);

        $this->work->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([
                $this->createObject('John'),
                $this->createObject('Henry'),
                $this->createObject('Arthur'),
                $this->createObject('Barnaby'),
                $this->createObject('Oliver'),
            ]);

        $this->assert->initialize($this->context);
        $this->assert->validate($this->createObject('Archie'), $this->entityAttribute);
    }
}
