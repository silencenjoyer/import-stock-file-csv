<?php

namespace App\Enum;

/**
 * This is the Enum that contains the column keys of the stock import file.
 *
 * The default values of the columns can be found in services.yml parameters.
 */
enum StockFileColumnEnum: string
{
    /** "Product Code" column header key. */
    case CODE = 'code';
    /** "Product Name" column header key. */
    case NAME = 'name';
    /** "Product Description" column header key. */
    case DESCRIPTION = 'description';
    /** "Stock" column header key. */
    case STOCK = 'stock';
    /** "Cost in GBP" (cost) column header key. */
    case COST = 'cost';
    /** "Discontinued" column header key. */
    case DISCONTINUED = 'discontinued';
}
