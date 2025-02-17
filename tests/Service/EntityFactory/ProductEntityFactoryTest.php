<?php

namespace App\Tests\Service\EntityFactory;

use App\Entity\Product;
use App\Enum\StockFileColumnEnum;
use App\Service\EntityFactory\ProductEntityFactory;
use App\ValueObject\FileHeaders;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductEntityFactoryTest extends KernelTestCase
{
    private ProductEntityFactory $factory;
    private FileHeaders $headers;
    private EventDispatcherInterface $dispatcher;

    /**
     * {@inheritDoc}
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = new FileHeaders([
            StockFileColumnEnum::CODE->value => StockFileColumnEnum::CODE->value,
            StockFileColumnEnum::NAME->value => StockFileColumnEnum::NAME->value,
            StockFileColumnEnum::DESCRIPTION->value => StockFileColumnEnum::DESCRIPTION->value,
            StockFileColumnEnum::STOCK->value => StockFileColumnEnum::STOCK->value,
            StockFileColumnEnum::COST->value => StockFileColumnEnum::COST->value,
            StockFileColumnEnum::DISCONTINUED->value => StockFileColumnEnum::DISCONTINUED->value,
        ]);

        self::bootKernel();
        $container = self::getContainer();

        $validator = $container->get('validator');
        $this->dispatcher = $container->get('event_dispatcher');

        $this->factory = new class($validator, $this->dispatcher) extends ProductEntityFactory
        {
            public function testPopulate(array $row, FileHeaders $headers): object
            {
                return $this->populate($row, $headers);
            }
        };
    }

    /**
     * Checks {@see Product} populating from array.
     *
     * @dataProvider getData
     * @param array $row
     * @return void
     */
    public function testCreate(array $row): void
    {
        $entity = $this->factory->testPopulate($row, $this->headers);

        $this->assertInstanceOf(Product::class, $entity);

        $this->assertEquals($row[$this->headers->get(StockFileColumnEnum::CODE)], $entity->getProductCode());
        $this->assertEquals($row[$this->headers->get(StockFileColumnEnum::NAME)], $entity->getProductName());
        $this->assertEquals($row[$this->headers->get(StockFileColumnEnum::DESCRIPTION)], $entity->getProductDesc());
        $this->assertEquals($row[$this->headers->get(StockFileColumnEnum::STOCK)], $entity->getProductStock());
        $this->assertEquals($row[$this->headers->get(StockFileColumnEnum::COST)], $entity->getProductCost());

        if ($row[$this->headers->get(StockFileColumnEnum::DISCONTINUED)] === 'yes') {
            $this->assertInstanceOf(DateTime::class, $entity->getDiscontinuedAt());
            $this->assertEquals(
                $entity->getDiscontinuedAt()->format('Y-m-d H:i:s'),
                (new DateTime())->format('Y-m-d H:i:s')
            );
        } else {
            $this->assertNull($entity->getDiscontinuedAt());
        }
    }

    /**
     * Data provider for the {@see Product} populating.
     *
     * @return array[]
     */
    public function getData(): array
    {
        return [
            [
                [
                    StockFileColumnEnum::CODE->value => 'P0001',
                    StockFileColumnEnum::NAME->value => 'TV',
                    StockFileColumnEnum::DESCRIPTION->value => 'Product description',
                    StockFileColumnEnum::STOCK->value => 5555,
                    StockFileColumnEnum::COST->value => 999,
                    StockFileColumnEnum::DISCONTINUED->value => 'yes',
                ]
            ],
            [
                [
                    StockFileColumnEnum::CODE->value => 'P0001',
                    StockFileColumnEnum::NAME->value => 'TV',
                    StockFileColumnEnum::DESCRIPTION->value => 'Product description',
                    StockFileColumnEnum::STOCK->value => 5555,
                    StockFileColumnEnum::COST->value => 999,
                    StockFileColumnEnum::DISCONTINUED->value => null,
                ]
            ]
        ];
    }
}
