<?php

namespace App\ValueObject;

use BackedEnum;
use Closure;

/**
 * A class that stores the mapping of file column names to their values.
 */
class FileHeaders
{
    /**
     * Must be a headers in the format [BackedEnum::case->value => 'Column Name']
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * @param array $headers
     */
    public function __construct(
        array $headers
    ) {
        $this->set($headers);
    }

    /**
     * Setter for the {@see $headers}.
     *
     * @param array $headers
     * @return void
     */
    public function set(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Current {@see $headers} getter.
     *
     * @return string[]
     */
    public function all(): array
    {
        return $this->headers;
    }

    /**
     * Checks if the provided BackedEnum value is in the current {@see $headers}.
     *
     * @param BackedEnum $key
     * @return bool
     */
    public function has(BackedEnum $key): bool
    {
        return isset($this->headers[$key->value]);
    }

    /**
     * Provides header from {@see $headers} by BackedEnum value.
     *
     * @param BackedEnum $key
     * @return string|null
     */
    public function get(BackedEnum $key): ?string
    {
        return $this->headers[$key->value] ?? null;
    }

    /**
     * Applies a custom function to convert {@see $headers}.
     *
     * @param Closure $mapper
     * @return void
     */
    public function map(Closure $mapper): void
    {
        $this->headers = array_map($mapper, $this->headers);
    }
}
