<?php

namespace App\Service\FileReader;

use Traversable;

/**
 * Interface for readers of the files.
 *
 * It provides a method for setting the path to the file to be read, and a method for reading that implies line-by-line
 * reading for import system performance.
 */
interface FileReaderInterface
{
    /**
     * This method should tell the reader the path to the destination file.
     *
     * @param string $path
     * @return self
     */
    public function setFilePath(string $path): self;

    /**
     * This is a method of extracting data from a file that implies line-by-line reading.
     *
     * @return Traversable
     */
    public function getByRows(): Traversable;
}
