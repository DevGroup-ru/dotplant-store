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
        $priceArray = $price->getLastPrice();
        return $priceArray !== false
            ? self::applyExtendedPrice($price->getGoods(), $priceArray)
            : false;
    }
}
