<?php

namespace DotPlant\Store\components\calculator;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\helpers\ExtendedPriceHelper;
use DotPlant\Store\interfaces\CalculatorInterface;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\goods\Goods;

/**
 * Class Calculator
 *
 * @package DotPlant\Store\components\calculator
 */
abstract class Calculator implements CalculatorInterface
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

        if (empty($extendedPrices) !== true) {
            $priceArray['original_value'] = $priceArray['value'];
            $priceArray['reason'] = [];
            foreach ($extendedPrices as $extendedPrice) {
                $priceBefore = $priceArray['value'];
                $extendedPriceValue = $extendedPrice['value'];
                if (empty($extendedPrice['currency_iso_code']) === false && $extendedPrice['currency_iso_code'] !== $priceArray['iso_code'] && $extendedPrice['mode'] != ExtendedPrice::MODE_PERCENTAGE) {
                    $extendedPriceValue = CurrencyHelper::convertCurrencies(
                        $extendedPriceValue,
                        CurrencyHelper::findCurrencyByIso($extendedPrice['currency_iso_code']),
                        CurrencyHelper::findCurrencyByIso($priceArray['iso_code'])
                    );
                }

                switch ($extendedPrice['mode']) {
                    case ExtendedPrice::MODE_AMOUNT:
                        $priceArray['value'] -= $extendedPriceValue;
                        break;
                    case ExtendedPrice::MODE_PERCENTAGE:
                        $priceArray['value'] *= (1 - $extendedPrice['value'] / 100);
                        break;
                    case ExtendedPrice::MODE_DEFINE:
                        $priceArray['value'] = $extendedPriceValue;
                        break;
                }

                $minPrice = $extendedPrice['min_product_price'];
                if (is_null($minPrice) === false) {
                    if ($minPrice < $priceBefore) {
                        $priceArray['value'] = max($priceArray['value'], $minPrice);
                    } else {
                        $priceArray['value'] = $priceBefore;
                    }
                }


                $priceArray['reason'][] = [
                    'extended_price_id' => $extendedPrice['id'],
                    'name' => $extendedPrice['name'],
                    'value' => $priceArray['value'] - $priceBefore,
                ];
            }
        }

        return $priceArray;
    }
}
