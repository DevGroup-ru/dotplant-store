<?php

namespace DotPlant\Store\components\calculator;

use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\warehouse\Warehouse;

/**
 * Class OptionCalculator
 *
 * @package app\vendor\dotplant\store\src\components\calculator
 */
class OptionCalculator extends GoodsCalculator
{
    /**
     * @inheritdoc
     */
    public static function calculate(PriceInterface $price)
    {
        $result = [];
        $warehouse = Warehouse::getWarehouse(
            $price->getGoods()->id,
            $price->getWarehouseId(),
            false
        );
        if ($warehouse) {
            $result = self::applyExtendedPrice(
                $price->getGoods(),
                $warehouse->getPrice($price->getPriceType())
            );
        }
        return $result;
    }
}
