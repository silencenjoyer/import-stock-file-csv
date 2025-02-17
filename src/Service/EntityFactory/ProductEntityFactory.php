<?php

namespace App\Service\EntityFactory;

use App\Entity\Product;
use App\Enum\StockFileColumnEnum;
use App\ValueObject\FileHeaders;
use DateTime;

/**
 * {@inheritDoc}
 *
 * This implementation adds logic to create the {@see Product} entity.
 */
class ProductEntityFactory extends AbstractEntityFactory
{
    /**
     * {@inheritDoc}
     *
     * This realization creates a {@see Product} entity and fills it with data.
     *
     * @param array $row
     * @param FileHeaders $headers
     * @return Product
     */
    protected function populate(array $row, FileHeaders $headers): object
    {
        $product = new Product();
        $product->setProductCode($row[$headers->get(StockFileColumnEnum::CODE)]);
        $product->setProductName($row[$headers->get(StockFileColumnEnum::NAME)]);
        $product->setProductDesc($row[$headers->get(StockFileColumnEnum::DESCRIPTION)]);
        $product->setProductStock($row[$headers->get(StockFileColumnEnum::STOCK)]);
        $product->setProductCost($row[$headers->get(StockFileColumnEnum::COST)]);

        if ($row[$headers->get(StockFileColumnEnum::DISCONTINUED)] ?? false) {
            $product->setDiscontinuedAt(new DateTime());
        }

        return $product;
    }
}
