<?php

namespace App\Tests\ValueObject;

use App\ValueObject\ImportResult;
use PHPUnit\Framework\TestCase;

class ImportResultTest extends TestCase
{
    private ImportResult $importResult;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importResult = new ImportResult();
    }

    /**
     * @return ImportResult
     */
    public function testDefaultSuccess(): ImportResult
    {
        $this->assertEquals(0, $this->importResult->getSuccess());
        return $this->importResult;
    }

    /**
     * @depends testDefaultSuccess
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testDefaultSkipped(ImportResult $result): ImportResult
    {
        $this->assertEquals(0, $result->getSkipped());
        return $result;
    }

    /**
     * @depends testDefaultSkipped
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testDefaultProcessed(ImportResult $result): ImportResult
    {
        $this->assertEquals(0, $result->getProcessed());
        return $result;
    }

    /**
     * @depends testDefaultProcessed
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testIncrementProcessed(ImportResult $result): ImportResult
    {
        $result->incrementProcessed();
        $this->assertEquals(1, $result->getProcessed());

        $result->incrementProcessed(10);
        $this->assertEquals(11, $result->getProcessed());

        return $result;
    }

    /**
     * @depends testIncrementProcessed
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testIncrementSkipped(ImportResult $result): ImportResult
    {
        $result->incrementSkipped();
        $this->assertEquals(1, $result->getSkipped());

        $result->incrementSkipped(5);
        $this->assertEquals(6, $result->getSkipped());

        return $result;
    }

    /**
     * @depends testIncrementSkipped
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testGetSuccess(ImportResult $result): ImportResult
    {
        $this->assertEquals(11 - 6, $result->getSuccess());

        return $result;
    }

    /**
     * @depends testGetSuccess
     * @param ImportResult $result
     * @return ImportResult
     */
    public function testHasNotErrors(ImportResult $result): ImportResult
    {
        $this->assertFalse($result->hasErrors());
        return $result;
    }

    /**
     * @depends testHasNotErrors
     * @param ImportResult $result
     * @return void
     */
    public function testSetErrors(ImportResult $result): void
    {
        $result->addErrors(['test' => 1], ['test' => 'error']);
        $this->assertTrue($result->hasErrors());

        $this->assertEquals(
            [
                [
                    ['test' => 1],
                    ['test' => 'error'],
                ]
            ],
            $result->getErrors()
        );
    }
}
