<?php

namespace App\Tests\Service\FileReader;

use App\Service\FileReader\CsvFileReader;
use League\Csv\Exception;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;

class CsvFileReaderTest extends TestCase
{
    private CsvFileReader $fileReader;
    private string $tempFile;

    /**
     * Creates a temporary test file and stores the path to it.
     *
     * @return void
     */
    protected function setTempFile(): void
    {
        $tempFile = tmpfile();
        $metaData = stream_get_meta_data($tempFile);
        $this->tempFile = $metaData['uri'];
        fclose($tempFile);
    }

    /**
     * {@inheritDoc}
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->fileReader = new CsvFileReader(new Filesystem());
    }

    /**
     * {@inheritDoc}
     *
     * This implementation deletes the temporary test file and its path, if one was created.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (isset($this->tempFile)) {
            unlink($this->tempFile);
            unset($this->tempFile);
        }
    }

    /**
     * @dataProvider getNonExistentFilePaths
     *
     * @param string $path
     * @return void
     */
    public function testSetNonExistentFilePath(string $path): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->fileReader->setFilePath($path);
    }

    /**
     * @dataProvider getCorrectFilePaths
     *
     * @param string $path
     * @return void
     */
    public function testSetCorrectFilePath(string $path): void
    {
        $this->expectNotToPerformAssertions();

        $this->fileReader->setFilePath($path);
    }

    /**
     * @dataProvider getIncorrectExtensionFilePaths
     * @param string $path
     * @return void
     */
    public function testSetIncorrectExtensionFilePath(string $path): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->fileReader->setFilePath($path);
    }

    /**
     * @dataProvider getCorrectData
     *
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    public function testReadCorrectFile(array $write, array $expectedData): void
    {
        $this->writeCsvToFile($write);
        $this->assertSame(
            $expectedData,
            iterator_to_array($this->fileReader->setFilePath($this->tempFile)->getByRows(), false)
        );
    }

    /**
     * Data provider for file paths that have an invalid types.
     *
     * @return array[]
     */
    public function getIncorrectExtensionFilePaths(): array
    {
        return [
            [__DIR__ . '/../../files/test.txt'],
        ];
    }

    /**
     * Data provider for non-existent file paths.
     *
     * @return array[]
     */
    public function getNonExistentFilePaths(): array
    {
        return [
            [__DIR__ . '/../../test_incorrect.csv'],
            [__DIR__ . '/../../test'],
        ];
    }

    /**
     * Data provider for correct file paths.
     *
     * @return array[]
     */
    public function getCorrectFilePaths(): array
    {
        return [
            [__DIR__ . '/../../files/stock.csv'],
        ];
    }

    /**
     * Writes the array data to a temporary test csv file.
     *
     * @param array $write
     * @return void
     */
    protected function writeCsvToFile(array $write): void
    {
        $this->setTempFile();
        $tempFile = fopen($this->tempFile, 'w');
        foreach ($write as $row) {
            fputcsv($tempFile, $row);
        }
        rewind($tempFile);
        fclose($tempFile);
    }

    /**
     * Provides the contents of the test file to be written in the first element.
     * In the second, contains the expected read value.
     *
     * @return iterable
     */
    public function getCorrectData(): iterable
    {
        return [
            # 1
            [
                [
                    ['Code', 'Name', 'Email'],
                    ['P0001', 'Test First', 'first@example.com'],
                    ['P0002', 'Test Second', 'second@example.com'],
                ],
                [
                    ['code' => 'P0001', 'name' => 'Test First', 'email' => 'first@example.com'],
                    ['code' => 'P0002', 'name' => 'Test Second', 'email' => 'second@example.com'],
                ]
            ],
            # 2
            [
                [
                    ['Product Code', 'Product Name', 'Provider Email'],
                    ['P0001', 'TV', 'first@example.com'],
                    ['P0002', 'TV 42 inch', 'second@example.com'],
                ],
                [
                    ['product_code' => 'P0001', 'product_name' => 'TV', 'provider_email' => 'first@example.com'],
                    ['product_code' => 'P0002', 'product_name' => 'TV 42 inch', 'provider_email' => 'second@example.com'],
                ]
            ],
            # 3
            [
                [
                    [
                        mb_convert_encoding('Code', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('Name', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('Email', 'ISO-8859-1', 'UTF-8'),
                    ],
                    [
                        mb_convert_encoding('P0001', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('Test First', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('first@example.com', 'ISO-8859-1', 'UTF-8'),
                    ],
                    [
                        mb_convert_encoding('P0002', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('Test Second', 'ISO-8859-1', 'UTF-8'),
                        mb_convert_encoding('second@example.com', 'ISO-8859-1', 'UTF-8')
                    ],
                ],
                [
                    ['code' => 'P0001', 'name' => 'Test First', 'email' => 'first@example.com'],
                    ['code' => 'P0002', 'name' => 'Test Second', 'email' => 'second@example.com'],
                ],
            ]
        ];
    }
}
