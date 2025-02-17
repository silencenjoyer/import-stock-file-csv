<?php

namespace App\Service\Parser;

use App\ValueObject\FileHeaders;
use App\ValueObject\ImportResult;
use Traversable;

/**
 * File row parsers interface.
 */
interface FileParserInterface
{
    /**
     * Method should provide processing of the file rows.
     *
     * @param Traversable $rowData
     * @param FileHeaders $headers
     * @param ImportResult $result
     * @return Traversable
     */
    public function process(Traversable $rowData, FileHeaders $headers, ImportResult $result): Traversable;
}
