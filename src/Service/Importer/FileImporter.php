<?php

namespace App\Service\Importer;

use App\Service\EntityStorage\EntityStorageInterface;
use App\Service\Event\InvalidEntityEvent;
use App\Service\FileReader\FileReaderInterface;
use App\Service\Parser\FileParserInterface;
use App\Service\RowValidator\RowValidatorInterface;
use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * A file importer that operates on file readers, row parsers, and storages for saving entities.
 */
class FileImporter implements FileImporterInterface
{
    public function __construct(
        protected RowValidatorInterface $validator,
        protected FileReaderInterface $reader,
        protected EntityStorageInterface $entityStorage,
        protected FileParserInterface $parser,
        protected EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * The {@see ImportResult} fabric method.
     *
     * @return ImportResult
     */
    protected function createResult(): ImportResult
    {
        return new ImportResult();
    }

    /**
     * This method performs file import by operating on file readers, row parsers, and storages for saving entities.
     *
     * {@inheritDoc}
     *
     * @return ImportResult
     */
    public function import(string $filePath, FileHeaders $headers): ImportResult
    {
        $result = $this->createResult();

        $this->dispatcher->addListener(
            InvalidEntityEvent::class,
            function (InvalidEntityEvent $event) use ($result, $headers) {
                $result->incrementSkipped();
                $errors = [];
                foreach ($event->getConstraints() as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                $result->addErrors($event->getEntity(), $errors);
            }
        );

        $rowData = $this->reader->setFilePath($filePath)->getByRows();

        $this->entityStorage->store(
            $this->parser->process($rowData, $headers, $result),
            $headers
        );

        return $result;
    }
}
