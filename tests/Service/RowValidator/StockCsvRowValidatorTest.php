<?php

namespace App\Tests\Service\RowValidator;

use App\Enum\StockFileColumnEnum;
use App\Service\RowValidator\StockCsvRowValidator;
use App\ValueObject\FileHeaders;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockCsvRowValidatorTest extends KernelTestCase
{
    private StockCsvRowValidator $validator;
    private FileHeaders $stockFileHeaders;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = static::getContainer();

        $this->validator = new StockCsvRowValidator(
            $container->get('validator')
        );
        $this->stockFileHeaders = new FileHeaders([
            StockFileColumnEnum::CODE->value => StockFileColumnEnum::CODE->value,
            StockFileColumnEnum::NAME->value => StockFileColumnEnum::NAME->value,
            StockFileColumnEnum::DESCRIPTION->value => StockFileColumnEnum::DESCRIPTION->value,
            StockFileColumnEnum::STOCK->value => StockFileColumnEnum::STOCK->value,
            StockFileColumnEnum::COST->value => StockFileColumnEnum::COST->value,
            StockFileColumnEnum::DISCONTINUED->value => StockFileColumnEnum::DISCONTINUED->value,
        ]);
    }

    /**
     * Checks if valid rows pass validation.
     *
     * @dataProvider getCorrectRows
     * @param array $row
     * @return void
     */
    public function testCorrectRows(array $row): void
    {
        $violations = $this->validator->validate($row, $this->stockFileHeaders);
        $this->assertCount(0, $violations);
    }

    /**
     * Verifies that incorrect rows do not pass validation.
     *
     * @dataProvider getIncorrectRows
     * @param array $row
     * @return void
     */
    public function testIncorrectRows(array $row): void
    {
        $violations = $this->validator->validate($row, $this->stockFileHeaders);
        $this->assertGreaterThanOrEqual(1, $violations->count());
    }

    /**
     * Data provider of correct row examples.
     *
     * @return array[]
     */
    public function getCorrectRows(): array
    {
        return [
            [
                [
                    StockFileColumnEnum::CODE->value => 'P12345',
                    StockFileColumnEnum::NAME->value => 'Product Name',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description.',
                    StockFileColumnEnum::STOCK->value => 50,
                    StockFileColumnEnum::COST->value => 100,
                    StockFileColumnEnum::DISCONTINUED->value => '',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P15241',
                    StockFileColumnEnum::NAME->value => 'Product Name 2',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => 500,
                    StockFileColumnEnum::COST->value => 500,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ]
        ];
    }

    /**
     * Data provider of incorrect row examples.
     *
     * @return array[]
     */
    public function getIncorrectRows(): array
    {
        return [
            [
                [
                    StockFileColumnEnum::CODE->value => 'P12345',
                    StockFileColumnEnum::NAME->value => 'Product Name',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description.',
                    StockFileColumnEnum::STOCK->value => 3,
                    StockFileColumnEnum::COST->value => 3,
                    StockFileColumnEnum::DISCONTINUED->value => '',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P15241',
                    StockFileColumnEnum::NAME->value => 'Product Name 2',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => -5,
                    StockFileColumnEnum::COST->value => -12,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P15241',
                    StockFileColumnEnum::NAME->value => 'Product Name 2',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => 30,
                    StockFileColumnEnum::COST->value => 1001,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P253416531764571635162417653761235145167351276351674176235',
                    StockFileColumnEnum::NAME->value => 'Product Name',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description.',
                    StockFileColumnEnum::STOCK->value => 30,
                    StockFileColumnEnum::COST->value => 15,
                    StockFileColumnEnum::DISCONTINUED->value => '',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => '',
                    StockFileColumnEnum::NAME->value => 'Product Name 2',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => 30,
                    StockFileColumnEnum::COST->value => 1001,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P0001',
                    StockFileColumnEnum::NAME->value => '',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => 30,
                    StockFileColumnEnum::COST->value => 1001,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P15241',
                    StockFileColumnEnum::DESCRIPTION->value => 'A sample product description 2.',
                    StockFileColumnEnum::STOCK->value => 30,
                    StockFileColumnEnum::COST->value => 1001,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ],
            ],
            [
                [],
            ],
        ];
    }
}
