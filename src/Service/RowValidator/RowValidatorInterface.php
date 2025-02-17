<?php

namespace App\Service\RowValidator;

use App\ValueObject\FileHeaders;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * An interface for row validators that should check the integrity of the data required for a particular entity.
 */
interface RowValidatorInterface
{
    /**
     * Row validating for a particular entity row.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return ConstraintViolationListInterface
     */
    public function validate(array $row, FileHeaders $headers):  ConstraintViolationListInterface;
}
