<?php

namespace App\ValueObject;

/**
 * The import result. Stores information about lines and errors.
 */
class ImportResult
{
    protected const int MIN_ALLOWED_RESULT = 0;

    /**
     * An indicator of how many lines were processed during the file import process.
     *
     * @var int
     */
    protected int $processed = 0;
    /**
     * An indicator of how many lines were skipped during the file import process.
     *
     * @var int
     */
    protected int $skipped = 0;
    /**
     * Errors in the file rows.
     *
     * @var array
     */
    protected array $errors = [];

    /**
     * Returns the $result parameter if it is greater than 0. Otherwise, returns 0.
     *
     * @param int $result
     * @return int
     */
    protected function wrapInt(int $result): int
    {
        return max(self::MIN_ALLOWED_RESULT, $result);
    }

    /**
     * Getter of the processed indicator {@see $processed}.
     *
     * @return int
     */
    public function getProcessed(): int
    {
        return $this->wrapInt($this->processed);
    }

    /**
     * Commit the processing.
     *
     * @param int $by How many to add.
     * @return $this
     */
    public function incrementProcessed(int $by = 1): self
    {
        $this->processed += $this->wrapInt($by);
        return $this;
    }

    /**
     * Getter of the skipped indicator {@see $skipped}.
     *
     * @return int
     */
    public function getSkipped(): int
    {
        return $this->wrapInt($this->skipped);
    }

    /**
     * Commit the skipping.
     *
     * @param int $by How many to add.
     * @return $this
     */
    public function incrementSkipped(int $by = 1): self
    {
        $this->skipped += $this->wrapInt($by);
        return $this;
    }

    /**
     * Provides counts of the successful.
     *
     * @return int
     */
    public function getSuccess(): int
    {
        return $this->getProcessed() - $this->getSkipped();
    }

    /**
     * Provides information about the existence of errors.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Errors getter {@see $errors}.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Appends errors.
     *
     * @param array|object $entity
     * @param array $errors
     * @return ImportResult
     */
    public function addErrors(array|object $entity, array $errors): self
    {
        $this->errors[] = [$entity, $errors];
        return $this;
    }
}
