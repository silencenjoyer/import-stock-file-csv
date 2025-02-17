<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Checks if the unique field value is in the buffer to be inserted.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class UniquePersistedEntity extends Constraint
{
    public string $message = 'This value is already used.';
    public array $fields = [];

    #[HasNamedArguments]
    public function __construct(
        array $fields = null,
        ?string $message = null,
        ?array $groups = null,
        $payload = null
    ) {
        parent::__construct(['fields' => $fields], $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    /**
     * {@inheritDoc}
     * @return string[]
     */
    public function getRequiredOptions(): array
    {
        return ['fields'];
    }

    /**
     * {@inheritDoc}
     * @return string|array|string[]
     */
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
