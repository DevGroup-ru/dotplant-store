<?php

namespace DotPlant\Store\components\calculator;

use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\warehouse\Warehouse;

/**
 * Class BundleCalculator
 *
 * @package DotPlant\Store\components\calculator
 */
class BundleCalculator extends GoodsCalculator
{

    public static function calculate(PriceInterface $price)
    {
        $result = [];

        $tempPrice = [];
        foreach ($price->getGoods()->getChildren()->select('id')->column() as $goodId) {
            $warehouse = Warehouse::getWarehouse(
                $goodId,
                $price->getWarehouseId(),
                false
            );
            if(!$warehouse) {
                return $result;
            }

        }


        return $result;
    }

}
