<?php

namespace App\Service\EntityStorage;

use App\Service\EntityFactory\EntityFactoryInterface;
use App\Service\Event\BeforePersistInStorageEvent;
use App\ValueObject\FileHeaders;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Traversable;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * A class that offers a basis for entity storing to the database.
 *
 * It retrieves data from the {@see Traversable}, passes it to the method to create entities, and saves it.
 * It also provides support for an {@see BeforePersistInStorageEvent} to stop saving.
 */
class DbEntityStorage implements EntityStorageInterface
{
    /**
     * The maximum batch insert size.
     * Will be used if the "%app.entity.storage.batch.size%" parameter is not set in "services.yml" file.
     */
    protected const int DEFAULT_BATCH_SIZE = 100;

    public function __construct(
        protected EntityFactoryInterface $entityFactory,
        protected EventDispatcherInterface $dispatcher,
        protected EntityManagerInterface $entityManager,
        #[Autowire('%app.entity.storage.batch.size%')]
        protected int $batchSize = self::DEFAULT_BATCH_SIZE
    ) {
    }

    /**
     * Flushes all changes to database and clears the ObjectManager.
     *
     * @return void
     */
    protected function flush(): void
    {
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * Creates and triggers an {@see BeforePersistInStorageEvent}.
     *
     * @param object $entity
     * @return BeforePersistInStorageEvent
     */
    protected function beforePersist(object $entity): BeforePersistInStorageEvent
    {
        $event = new BeforePersistInStorageEvent($entity);
        $this->dispatcher->dispatch($event);

        return $event;
    }

    /**
     * {@inheritDoc}
     *
     * This implementation proposes an entity save algorithm with support for an undo save event.
     *
     * @param Traversable $entityData
     * @param FileHeaders $headers
     * @return void
     */
    public function store(Traversable $entityData, FileHeaders $headers): void
    {
        $i = 0;
        foreach ($entityData as $row) {
            $entity = $this->entityFactory->create($row, $headers);

            if ($entity === null) {
                continue;
            }

            $event = $this->beforePersist($entity);

            if (!$event->isCancelled()) {
                $this->entityManager->persist($entity);
                $i++;
            }

            if ($i % $this->batchSize === 0) {
                $this->flush();
            }
        }

        if ($i % $this->batchSize !== 0) {
            $this->flush();
        }
    }
}
