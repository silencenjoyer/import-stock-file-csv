<?php

namespace App\Tests\ValueObject;

use App\Enum\StockFileColumnEnum;
use App\ValueObject\FileHeaders;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

class FileHeadersTest extends TestCase
{
    private FileHeaders $fileHeaders;
    private array $headersData;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->headersData = [
            StockFileColumnEnum::STOCK->value => 'Product Stock',
            StockFileColumnEnum::CODE->value => 'Product Code',
        ];

        $this->fileHeaders = new FileHeaders($this->headersData);
    }

    /**
     * Checking that the all method will return all headers.
     *
     * @return FileHeaders
     */
    public function testGetAll(): FileHeaders
    {
        $this->assertEquals($this->headersData, $this->fileHeaders->all());
        return $this->fileHeaders;
    }

    /**
     * Check to see if the method has worked.
     *
     * @depends testGetAll
     * @param FileHeaders $headers
     * @return FileHeaders
     */
    public function testHas(FileHeaders $headers): FileHeaders
    {
        $this->assertTrue($headers->has(StockFileColumnEnum::STOCK));
        return $headers;
    }

    /**
     * Check to see if the method has worked.
     *
     * @depends testHas
     * @param FileHeaders $headers
     * @return FileHeaders
     */
    public function testHasNot(FileHeaders $headers): FileHeaders
    {
        $this->assertFalse($headers->has(StockFileColumnEnum::DISCONTINUED));
        return $headers;
    }

    /**
     * Check that the get method returns a valid value for the passed key.
     *
     * @depends testHasNot
     * @param FileHeaders $headers
     * @return FileHeaders
     */
    public function testGet(FileHeaders $headers): FileHeaders
    {
        $this->assertEquals($this->headersData[StockFileColumnEnum::STOCK->value], $headers->get(StockFileColumnEnum::STOCK));
        return $headers;
    }

    /**
     * Check that the conversion method is working correctly.
     *
     * @depends testGet
     * @param FileHeaders $headers
     * @return FileHeaders
     */
    public function testMap(FileHeaders $headers): FileHeaders
    {
        $headers->map(fn(string $v) => (new UnicodeString($v))->snake()->toString());

        $this->assertEquals(
            [StockFileColumnEnum::STOCK->value => 'product_stock', StockFileColumnEnum::CODE->value => 'product_code'],
            $headers->all()
        );

        return $headers;
    }

    /**
     * Check to see if the method set worked.
     *
     * @depends testMap
     * @param FileHeaders $headers
     * @return void
     */
    public function testSet(FileHeaders $headers): void
    {
        $test = ['test' => 'Test', 'test_2' => 'Test 2'];

        $headers->set($test);

        $this->assertEquals($test, $headers->all());
    }
}
