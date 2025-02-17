<?php

namespace App\Service\Importer;

use App\ValueObject\ImportResult;
use App\ValueObject\FileHeaders;

/**
 * Interface for components that do file imports.
 */
interface FileImporterInterface
{
    /**
     * This method should contain logic specific to importing a file and return an {@see ImportResult} object containing
     * details on the operation.
     *
     * @param string $filePath path to the imported file.
     * @param FileHeaders $headers mapping file column names to their values.
     * @return ImportResult
     */
    public function import(string $filePath, FileHeaders $headers): ImportResult;
}
