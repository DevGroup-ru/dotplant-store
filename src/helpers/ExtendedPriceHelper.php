<?php

namespace DotPlant\Store\helpers;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Cart;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Json;

/**
 * Class ExtendedPriceHelper
 * @package DotPlant\Store\helpers
 */
class ExtendedPriceHelper
{
    /**
     * @var array
     */
    private static $_allGoodsExtendedPrices = [];

    /**
     * @var array
     */
    private static $_allOrdersExtendedPrices = [];


    /**
     * @var array
     */
    private static $_goodsExtendedPriceMap = [];

    /**
     * @var array
     */
    private static $_cartExtendedPriceMap = [];


    /**
     * @return ActiveQuery
     */
    private static function getExtendedPriceQuery()
    {
        return ExtendedPrice::find()->andWhere(
            ['OR', ['start_time' => null], ['>=', 'start_time', new Expression('NOW()')],]
        )->andWhere(
            ['OR', ['end_time' => null], ['<=', 'end_time', new Expression('NOW()')],]
        )->andWhere(
            ['OR', ['context_id' => null], ['context_id' => \Yii::$app->multilingual->context_id],]
        )->orderBy(
            ['is_final' => SORT_DESC, 'priority' => SORT_ASC]
        )->with(
            [
                'extendedPriceRules' => function (ActiveQuery $query) {
                    $query->orderBy(['priority' => SORT_ASC])->with('extendedPriceHandler');
                },
            ]
        );
    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    private static function getAllForGoods()
    {
        if (self::$_allGoodsExtendedPrices === []) {
            self::$_allGoodsExtendedPrices = self::getExtendedPriceQuery()->andWhere(
                ['calculator_type' => 'goods',]
            )->asArray()->all();
        }

        return self::$_allGoodsExtendedPrices;
    }

    /**
     *
     */
    private static function getAllForOrder()
    {
        if (self::$_allOrdersExtendedPrices === []) {
            self::$_allOrdersExtendedPrices = self::getExtendedPriceQuery()->andWhere(
                ['calculator_type' => 'order',]
            )->asArray()->all();
        }

        return self::$_allOrdersExtendedPrices;
    }

    /**
     * @param $goods
     *
     * @return array
     */
    private static function filterForGoods($goods)
    {
        if (isset(self::$_goodsExtendedPriceMap[$goods->id]) === false) {
            self::$_goodsExtendedPriceMap[$goods->id] = [];
            foreach (self::getAllForGoods() as $extendedPrice) {
                if (false === empty($extendedPrice['extendedPriceRules'])) {
                    $check = false;
                    foreach ($extendedPrice['extendedPriceRules'] as $rule) {
                        $class = $rule['extendedPriceHandler']['handler_class'];
                        $params = empty($rule['packed_json_params']) === false ? Json::decode(
                            $rule['packed_json_params']
                        ) : [];
                        $check = $class::check($goods, $params);

                        if (($check === false && $rule['operand'] === 'AND') || ($check === true && $rule['operand'] === 'OR')) {
                            break;
                        }
                    }
                    if ($check === true) {
                        self::$_goodsExtendedPriceMap[$goods->id][] = $extendedPrice;
                        if ((bool) $extendedPrice['is_final'] === true) {
                            break;
                        }
                    }
                }
            }
        }
        return self::$_goodsExtendedPriceMap[$goods->id];
    }

    /**
     * @param $cart
     *
     * @return array
     */
    private static function filterForCart($cart)
    {
        if (isset(self::$_cartExtendedPriceMap[$cart->id]) === false) {
            self::$_cartExtendedPriceMap[$cart->id] = [];
            //@todo add checking of target class
            foreach (self::getAllForOrder() as $extendedPrice) {
                if (false === empty($extendedPrice['extendedPriceRules'])) {
                    $check = false;
                    foreach ($extendedPrice['extendedPriceRules'] as $rule) {
                        $class = $rule['extendedPriceHandler']['handler_class'];
                        $params = empty($rule['packed_json_params']) === false ? Json::decode(
                            $rule['packed_json_params']
                        ) : [];
                        $check = $class::check($cart, $params);

                        if (($check === false && $rule['operand'] === 'AND') || ($check === true && $rule['operand'] === 'OR')) {
                            break;
                        }
                    }
                    if ($check === true) {
                        self::$_cartExtendedPriceMap[$cart->id][] = $extendedPrice;
                        if ((bool) $extendedPrice['is_final'] === true) {
                            break;
                        }
                    }
                }
            }
        }
        return self::$_cartExtendedPriceMap[$cart->id];
    }

    /**
     * @param $object
     *
     * @return array
     */
    public static function getForObject($object)
    {
        $result = [];

        if ($object instanceof Goods) {
            $result = self::filterForGoods($object);
        } elseif ($object instanceof Cart) {
            $result = self::filterForCart($object);
        } else {
            throw new InvalidParamException;
        }
        return $result;
    }

    /**
     * @param array $extendedPrices
     * @param float $priceBefore
     * @param string $priceBeforeIsoCode
     *
     * @return array
     */
    public static function applyExtendedPrices($extendedPrices, $priceBefore, $priceBeforeIsoCode)
    {
        $price = [
            'priceBefore' => $priceBefore,
            'priceAfter' => $priceBefore,
            'priceBeforeIsoCode' => $priceBeforeIsoCode,
            'extendedPrice' => [],
        ];
        if (empty($extendedPrices) === false) {
            foreach ($extendedPrices as $extendedPrice) {
                $currentPrice = $price['priceAfter'];
                $extendedPriceValue = CurrencyHelper::convertCurrencies(
                    $extendedPrice['value'],
                    CurrencyHelper::findCurrencyByIso($extendedPrice['currency_iso_code']),
                    CurrencyHelper::findCurrencyByIso($priceBeforeIsoCode)
                );

                switch ($extendedPrice['mode']) {
                    case ExtendedPrice::MODE_AMOUNT:

                        $price['priceAfter'] -= $extendedPriceValue;
                        break;
                    case ExtendedPrice::MODE_PERCENTAGE:
                        $price['priceAfter'] *= (1 - $extendedPrice['value'] / 100);
                        break;
                    case ExtendedPrice::MODE_DEFINE:
                        $price['priceAfter'] = $extendedPriceValue;
                        break;
                }

                $minPrice = CurrencyHelper::convertCurrencies(
                    $extendedPrice['min_product_price'],
                    CurrencyHelper::findCurrencyByIso($extendedPrice['currency_iso_code']),
                    CurrencyHelper::findCurrencyByIso($priceBeforeIsoCode)
                );
                if (is_null($minPrice) === false) {
                    if ($minPrice < $priceBefore) {
                        $price['priceAfter'] = max($price['priceAfter'], $minPrice);
                    } else {
                        $price['priceAfter'] = $price['priceBefore'];
                    }
                }

                $price['extendedPrice'][] = [
                    'extended_price_id' => $extendedPrice['id'],
                    'name' => $extendedPrice['name'],
                    'target_class' => $extendedPrice['target_class'],
                    'value' => $price['priceAfter'] - $currentPrice,
                ];
            }
        }
        return $price;
    }
}
