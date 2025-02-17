<?php

namespace App\Service\RowValidator;

use App\Entity\Product;
use App\Enum\StockFileColumnEnum;
use App\ValueObject\FileHeaders;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Row validator for product entity when importing a stock file.
 */
class StockCsvRowValidator implements RowValidatorInterface
{
    private const int CHEAP_MIN_QUANTITY = 10;
    private const int CHEAP_UP_TO = 5;
    private const int MAX_COST = 1000;

    protected ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * A method that provides symfony validation constraints for columns of products.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return Constraint
     */
    protected function constraint(array $row, FileHeaders $headers): Constraint
    {
        return new Assert\Collection(
            fields: [
                $headers->get(StockFileColumnEnum::CODE) => [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Length(['max' => Product::CODE_MAX_LENGTH]),
                ],
                $headers->get(StockFileColumnEnum::NAME) => [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Length(['max' => Product::NAME_MAX_LENGTH]),
                ],
                $headers->get(StockFileColumnEnum::DESCRIPTION) => [
                    new Assert\NotBlank(),
                    new Assert\Type('string'),
                    new Assert\Length(['max' => Product::DESCRIPTION_MAX_LENGTH]),
                ],
                $headers->get(StockFileColumnEnum::STOCK) => new Assert\Sequentially([
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                    new Assert\PositiveOrZero(),
                    new Assert\When(
                        sprintf('cost < %d', self::CHEAP_UP_TO),
                        [
                            new Assert\GreaterThan(
                                value: self::CHEAP_MIN_QUANTITY,
                                message: sprintf(
                                    'Must be greater than %d if the price is less than %d',
                                    self::CHEAP_MIN_QUANTITY,
                                    self::CHEAP_UP_TO
                                )
                            ),
                        ],
                        [
                            'cost' => $row[$headers->get(StockFileColumnEnum::COST)] ?? 0,
                        ]
                    ),
                ]),
                $headers->get(StockFileColumnEnum::COST) => [
                    new Assert\NotBlank(),
                    new Assert\Type('numeric'),
                    new Assert\LessThan(self::MAX_COST),
                ],
                $headers->get(StockFileColumnEnum::DISCONTINUED) => new Assert\Optional([
                    new Assert\Type('string'),
                    new Assert\Choice(['yes', '']),
                ]),
            ],
            allowExtraFields: true
        );
    }

    /**
     * Array structure checking.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return ConstraintViolationListInterface
     */
    public function validate(array $row, FileHeaders $headers):  ConstraintViolationListInterface
    {
        $constraint = $this->constraint($row, $headers);
        return $this->validator->validate($row, $constraint);
    }
}
