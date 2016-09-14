<?php

namespace DotPlant\Store\components\calculator;

use DotPlant\Store\interfaces\CalculatorInterface;
use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\warehouse\Warehouse;

/**
 * Class ProductCalculator
 *
 * @package DotPlant\Store\components\calculator
 */
class ProductCalculator implements CalculatorInterface
{
    public static function calculate(PriceInterface $price)
    {
        return Warehouse::getWarehouse(
            $price->getGoodsId(),
            $price->getWarehouseId(),
            false
        )->getPrice($price->getPriceType());
    }
}
