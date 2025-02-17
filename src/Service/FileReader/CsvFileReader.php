<?php

namespace App\Service\FileReader;

use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\InvalidArgumentException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\UnicodeString;
use Traversable;

/**
 * Class for readers of the CSV files.
 */
class CsvFileReader implements FileReaderInterface
{
    protected string $filePath;

    public function __construct(protected Filesystem $filesystem)
    {
    }

    /**
     * Checks if the path is correct and if the file located on the path is correct.
     *
     * @param string $filePath
     * @return void
     */
    protected function checkFilePath(string $filePath): void
    {
        if (!$this->filesystem->exists($filePath)) {
            throw new FileNotFoundException();
        }

        $file = new File($filePath);
        if ($file->getMimeType() !== 'text/csv') {
            throw new InvalidArgumentException('File extension must be "text/csv".');
        }
    }

    /**
     * This method tells the reader the path to the destination file.
     *
     * @param string $path
     * @return $this
     */
    public function setFilePath(string $path): self
    {
        $this->checkFilePath($path);

        $this->filePath = $path;
        return $this;
    }

    /**
     * Reads a file.
     *
     * {@inheritDoc}
     *
     * @throws UnavailableStream
     * @throws SyntaxError
     * @throws Exception
     */
    public function getByRows(): Traversable
    {
        $csv = Reader::createFromPath($this->filePath);
        $csv->setHeaderOffset(0);

        return $csv->mapHeader(
            array_map(fn(string $val) => (new UnicodeString($val))->snake()->toString(), $csv->getHeader())
        );
    }
}
