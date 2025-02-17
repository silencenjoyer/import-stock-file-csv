<?php

namespace App\Tests\Service\EntityStorage;

use App\Service\EntityFactory\EntityFactoryInterface;
use App\Service\EntityStorage\DbEntityStorage;
use App\Service\Event\BeforePersistInStorageEvent;
use App\ValueObject\FileHeaders;
use ArrayIterator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DbEntityStorageTest extends TestCase
{
    private const int BATCH_SIZE = 2;

    private EntityFactoryInterface $entityFactory;
    private EventDispatcherInterface $dispatcher;
    private EntityManagerInterface $entityManager;
    private DbEntityStorage $dbEntityStorage;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->entityFactory = $this->createMock(EntityFactoryInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->dbEntityStorage = new DbEntityStorage(
            $this->entityFactory,
            $this->dispatcher,
            $this->entityManager,
            self::BATCH_SIZE
        );
    }

    /**
     * Test that the store method correctly stores entities with packages.
     *
     * @dataProvider getRows
     * @param array $rows
     * @return void
     */
    public function testStoreWithBatchInsert(array $rows): void
    {
        $entityData = new ArrayIterator($rows);

        $headers = $this->createMock(FileHeaders::class);

        $this->entityFactory->method('create')
            ->willReturnCallback(fn(array $row) => (object) $row);

        $this->entityManager->expects($this->exactly($entityData->count()))
            ->method('persist');

        $this->entityManager->expects($this->exactly(ceil($entityData->count() / self::BATCH_SIZE)))
            ->method('flush');

        $this->entityManager->expects($this->exactly(ceil($entityData->count() / self::BATCH_SIZE)))
            ->method('clear');

        $this->dbEntityStorage->store($entityData, $headers);
    }

    /**
     * Test for the store method skipping entities that failed to be created.
     *
     * @return void
     */
    public function testStoreSkipsInvalidEntities(): void
    {
        $entityData = new ArrayIterator([
            ['code' => 'P001', 'name' => 'Product 1'],
            ['code' => 'P002', 'name' => ''],
            ['code' => 'P003', 'name' => 'Product 3'],
        ]);

        $headers = $this->createMock(FileHeaders::class);

        $this->entityFactory->method('create')
            ->willReturnCallback(function (array $row) {
                if ($row['name'] === '') {
                    return null;
                }
                return (object) $row;
            });

        $this->entityManager->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->dbEntityStorage->store($entityData, $headers);
    }

    /**
     * Test that the store method does not save entities if the event is canceled.
     *
     * @return void
     */
    public function testStoreSkipsCancelledEvents(): void
    {
        $entityData = new ArrayIterator([
            ['code' => 'P001', 'name' => 'Product 1'],
            ['code' => 'P002', 'name' => 'Product 2'],
        ]);

        $headers = $this->createMock(FileHeaders::class);

        $this->entityFactory->method('create')
            ->willReturnCallback(function (array $row) {
                return (object) $row;
            });

        $this->dispatcher->method('dispatch')
            ->willReturnCallback(function (BeforePersistInStorageEvent $event) {
                if ($event->getEntity()->code === 'P002') {
                    $event->cancelSave();
                }
                return $event;
            });

        $this->entityManager->expects($this->once())
            ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->dbEntityStorage->store($entityData, $headers);
    }

    /**
     * Data provider for batch size test.
     *
     * @return array[]
     */
    public function getRows(): array
    {
        return [
            [
                [
                    ['code' => 'P001', 'name' => 'Product 1'],
                    ['code' => 'P002', 'name' => 'Product 2'],
                    ['code' => 'P003', 'name' => 'Product 3'],
                ],
            ],
            [
                [
                    ['code' => 'P001', 'name' => 'Product 1'],
                    ['code' => 'P002', 'name' => 'Product 2'],
                    ['code' => 'P004', 'name' => 'Product 4'],
                    ['code' => 'P005', 'name' => 'Product 5'],
                    ['code' => 'P006', 'name' => 'Product 6'],
                    ['code' => 'P007', 'name' => 'Product 7'],
                    ['code' => 'P008', 'name' => 'Product 8'],
                    ['code' => 'P009', 'name' => 'Product 9'],
                ],
            ],
        ];
    }
}
