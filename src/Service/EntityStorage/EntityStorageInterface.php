<?php

namespace App\Service\EntityStorage;

use App\ValueObject\FileHeaders;
use Traversable;

/**
 * An interface for classes that must provide a way to store entities.
 */
interface EntityStorageInterface
{
    /**
     * A method that should save entities by retrieving data for their properties from the {@see Traversable}.
     *
     * @param Traversable $entityData
     * @param FileHeaders $headers
     * @return void
     */
    public function store(Traversable $entityData, FileHeaders $headers): void;
}
