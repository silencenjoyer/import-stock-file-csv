<?php

namespace App\Tests\Service\Parser;

use App\Service\Event\InvalidEntityEvent;
use App\Service\Parser\FileParser;
use App\Service\RowValidator\RowValidatorInterface;
use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FileParserTest extends TestCase
{
    private FileParser $fileParser;
    private RowValidatorInterface $validator;
    private EventDispatcherInterface $dispatcher;
    private ImportResult $importResult;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(RowValidatorInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->importResult = new ImportResult();

        $this->fileParser = new FileParser($this->validator, $this->dispatcher);
    }

    /**
     * Check that the process method returns correct data if the validation was successful.
     *
     * @dataProvider getValidRows
     *
     * @param array $rows
     * @return void
     */
    public function testProcessWithValidData(array $rows): void
    {
        $rowsCount = count($rows);
        $rowData = new ArrayIterator($rows);

        $headers = $this->createMock(FileHeaders::class);

        $this->validator->expects($this->exactly($rowsCount))
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $result = iterator_to_array($this->fileParser->process($rowData, $headers, $this->importResult));

        $this->assertCount($rowsCount, $result);

        for ($i = 0; $i < $rowsCount; $i++) {
            $this->assertEquals($rows[$i], $result[$i]);
        }

        $this->assertEquals(2, $this->importResult->getProcessed());
    }

    /**
     * Check that the process method correctly handles validation errors and dispatches the event.
     *
     * @return void
     */
    public function testProcessWithInvalidData(): void
    {
        $rowData = new ArrayIterator([
            ['code' => 'P001', 'name' => 'Product 1'],
            ['code' => 'P002', 'name' => ''],
        ]);

        $headers = $this->createMock(FileHeaders::class);

        $this->validator->method('validate')
            ->willReturnCallback(function (array $record) {
                if ($record['name'] === '') {
                    return new ConstraintViolationList([$this->createMock(ConstraintViolation::class)]);
                }
                return new ConstraintViolationList();
            });

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (InvalidEntityEvent $event) {
                $this->assertEquals(['code' => 'P002', 'name' => ''], $event->getEntity());
                $this->assertCount(1, $event->getConstraints());
                return true;
            }));

        $result = iterator_to_array($this->fileParser->process($rowData, $headers, $this->importResult));

        $this->assertCount(1, $result);
        $this->assertEquals(['code' => 'P001', 'name' => 'Product 1'], $result[0]);

        $this->assertEquals(2, $this->importResult->getProcessed());
    }

    /**
     * Check that the process method correctly handles empty data.
     *
     * @return void
     */
    public function testProcessWithEmptyData(): void
    {
        $rowData = new ArrayIterator([]);
        $headers = $this->createMock(FileHeaders::class);

        $this->validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $result = iterator_to_array($this->fileParser->process($rowData, $headers, $this->importResult));

        $this->assertCount(0, $result);

        $this->assertEquals(0, $this->importResult->getProcessed());
    }

    /**
     * Data provider of the correct rows data.
     *
     * @return array[]
     */
    public function getValidRows(): array
    {
        return [
            [
                [
                    ['code' => 'P001', 'name' => 'Product 1'],
                    ['code' => 'P002', 'name' => 'Product 2'],
                ],
            ]
        ];
    }
}
