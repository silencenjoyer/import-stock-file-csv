<?php

namespace App\Service\Parser;

use App\Service\Event\InvalidEntityEvent;
use App\Service\RowValidator\RowValidatorInterface;
use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Traversable;

/**
 * A file row parser that interacts with row validators.
 */
class FileParser implements FileParserInterface
{
    public function __construct(
        protected RowValidatorInterface $validator,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * Method that processes file rows, interacting with row validators to guarantee the integrity of the string data
     * and populating the import result.
     *
     * @param Traversable $rowData
     * @param FileHeaders $headers
     * @param ImportResult $result
     * @return Traversable
     */
    public function process(Traversable $rowData, FileHeaders $headers, ImportResult $result): Traversable
    {
        foreach ($rowData as $record) {
            $result->incrementProcessed();

            $validationErrors = $this->validator->validate($record, $headers);

            if ($validationErrors->count()) {
                $event = new InvalidEntityEvent();
                $event->setEntity($record);
                $event->setConstraints($validationErrors);

                $this->dispatcher->dispatch($event);
                continue;
            }

            yield $record;
        }
    }
}
