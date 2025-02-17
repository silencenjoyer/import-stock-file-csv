<?php

namespace App\Service\EntityFactory;

use App\ValueObject\FileHeaders;

/**
 * Entity factory interface.
 */
interface EntityFactoryInterface
{
    /**
     * Must create an entity from an array and file header values.
     *
     * If the entity fails any checks, you should return null.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return object|null
     */
    public function create(array $row, FileHeaders $headers): ?object;
}
