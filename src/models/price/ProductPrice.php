<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\ProductCalculator;

/**
 * Class ProductPrice
 *
 * @package DotPlant\Store\models\price
 */
class ProductPrice extends Price
{
    protected $_calculatorClass = ProductCalculator::class;
}