<?php

namespace App\Tests\Service\Importer;

use App\Service\EntityStorage\EntityStorageInterface;
use App\Service\FileReader\FileReaderInterface;
use App\Service\Importer\FileImporter;
use App\Service\Parser\FileParserInterface;
use App\Service\RowValidator\RowValidatorInterface;
use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolation;

class FileImporterTest extends TestCase
{
    private RowValidatorInterface $validator;
    private FileReaderInterface $reader;
    private EntityStorageInterface $entityStorage;
    private FileParserInterface $parser;
    private EventDispatcherInterface $dispatcher;
    private FileImporter $fileImporter;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(RowValidatorInterface::class);
        $this->reader = $this->createMock(FileReaderInterface::class);
        $this->entityStorage = $this->createMock(EntityStorageInterface::class);
        $this->parser = $this->createMock(FileParserInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->fileImporter = new FileImporter(
            $this->validator,
            $this->reader,
            $this->entityStorage,
            $this->parser,
            $this->dispatcher
        );
    }

    /**
     * Check that the import method processes the data correctly and returns the result.
     *
     * @return void
     */
    public function testImportWithValidData(): void
    {
        $filePath = '/path/to/file.csv';
        $headers = $this->createMock(FileHeaders::class);
        $rowData = new ArrayIterator([
            ['code' => 'P001', 'name' => 'Product 1'],
            ['code' => 'P002', 'name' => 'Product 2'],
        ]);

        $this->reader->method('setFilePath')
            ->with($filePath)
            ->willReturn($this->reader);

        $this->reader->method('getByRows')
            ->willReturn($rowData);

        $this->parser->method('process')
            ->with($rowData, $headers, $this->isInstanceOf(ImportResult::class))
            ->willReturnCallback(function (ArrayIterator $rowData, FileHeaders $headers, ImportResult $result) {
                $result->incrementProcessed($rowData->count());
                return $rowData;
            });

        $this->entityStorage->expects($this->once())
            ->method('store')
            ->with($rowData, $headers);

        $result = $this->fileImporter->import($filePath, $headers);

        $this->assertEquals($rowData->count(), $result->getProcessed());
        $this->assertEquals($rowData->count(), $result->getSuccess());
        $this->assertEquals(0, $result->getSkipped());
    }

    /**
     * Check that the import method handles validation errors correctly.
     *
     * @return void
     */
    public function testImportWithInvalidData(): void
    {
        $filePath = '/path/to/file.csv';
        $headers = $this->createMock(FileHeaders::class);
        $rowData = new ArrayIterator([
            ['code' => 'P001', 'name' => 'Product 1'],
            ['code' => 'P002', 'name' => ''],
        ]);

        $this->reader->method('setFilePath')
            ->with($filePath)
            ->willReturn($this->reader);

        $this->reader->method('getByRows')
            ->willReturn($rowData);

        $this->parser->method('process')
            ->with($rowData, $headers, $this->isInstanceOf(ImportResult::class))
            ->willReturnCallback(function (ArrayIterator $rowData, FileHeaders $headers, ImportResult $result) {
                $violation = new ConstraintViolation(
                    'Name cannot be empty',
                    null,
                    [],
                    null,
                    'name',
                    ''
                );

                $result->addErrors(['code' => 'P002', 'name' => ''], ['name' => $violation->getMessage()]);

                $result->incrementProcessed($rowData->count());
                $result->incrementSkipped();
                return $rowData;
            });

        $this->entityStorage->expects($this->once())
            ->method('store')
            ->with($rowData, $headers);

        $result = $this->fileImporter->import($filePath, $headers);

        $this->assertEquals(2, $result->getProcessed());
        $this->assertEquals(1, $result->getSuccess());
        $this->assertEquals(1, $result->getSkipped());

        $errors = $result->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals('Name cannot be empty', $errors[0][1]['name']);
    }
}
