<?php

namespace DotPlant\Store\helpers;

use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Order;
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
    private static $_gooodsExtendedPriceMap = [];


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
                        $params = empty($rule['packed_json_params']) === false ? Json::decode($rule['packed_json_params']) : [];
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
     * @param $order
     *
     * @return array
     */
    private static function filterForOrder($order)
    {
        return [];
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
        } elseif ($object instanceof Order) {
            $result = self::filterForOrder($object);
        } else {
            throw new InvalidParamException;
        }
        return $result;
    }
}
