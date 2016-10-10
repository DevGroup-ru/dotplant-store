<?php

namespace DotPlant\Store\components\calculator;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\helpers\ExtendedPriceHelper;
use DotPlant\Store\interfaces\GoodsCalculatorInterface;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\goods\Goods;
use yii\helpers\ArrayHelper;

/**
 * Class Calculator
 *
 * @package DotPlant\Store\components\calculator
 */
abstract class GoodsCalculator implements GoodsCalculatorInterface
{
    /**
     * @param Goods $goods
     * @param $priceArray
     *
     * @return array
     */
    protected static function applyExtendedPrice(Goods $goods, $priceArray)
    {
        $extendedPrices = ExtendedPriceHelper::getForObject($goods);

        $resultPrice = ExtendedPriceHelper::applyExtendedPrices(
            $extendedPrices,
            $priceArray['value'],
            $priceArray['isoCode']
        );

        $priceArray['valueWithoutDiscount'] = $priceArray['value'];
        $priceArray['value'] = $resultPrice['priceAfter'];
        $priceArray['discountReasons'] = ArrayHelper::merge(
            ArrayHelper::getValue($priceArray, 'reason', []),
            $resultPrice['extendedPrice']
        );

        return $priceArray;
    }
}
