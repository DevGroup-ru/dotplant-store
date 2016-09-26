<?php


namespace DotPlant\Store\components\calculator;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\OrderItem;
use DotPlant\Store\models\price\Price;
use yii\base\InvalidParamException;

class OrderItemCalculator
{
    /**
     * Calculates object price
     *
     * @param OrderItem $orderItem
     *
     * @return array
     */
    public static function getPrice($orderItem)
    {
        if ($orderItem instanceof OrderItem === false) {
            throw new InvalidParamException;
        }
        $price = ['totalPriceWithoutDiscount' => 0, 'totalPriceWithDiscount' => 0, 'items' => 0, 'extendedPrice' => []];
        $priceType = $orderItem->cart->is_retail == 1 ? Price::TYPE_RETAIL : Price::TYPE_WHOLESALE;

        $goodsId = $orderItem->goods_id;

        $goods = Goods::get($goodsId);
        $goodsPrice = $goods->getPrice($orderItem->warehouse_id, $priceType);
        $price['totalPriceWithDiscount'] = CurrencyHelper::convertCurrencies(
                $goodsPrice['value'],
                CurrencyHelper::findCurrencyByIso($goodsPrice['iso_code']),
                CurrencyHelper::findCurrencyByIso($orderItem->cart->currency_iso_code)
            ) * $orderItem->quantity;
        $price['totalPriceWithoutDiscount'] = CurrencyHelper::convertCurrencies(
                isset($goodsPrice['original_value']) ? $goodsPrice['original_value'] : $goodsPrice['value'],
                CurrencyHelper::findCurrencyByIso($goodsPrice['iso_code']),
                CurrencyHelper::findCurrencyByIso($orderItem->cart->currency_iso_code)
            ) * $orderItem->quantity;
        $price['items'] = $orderItem->quantity;
        $price['extendedPrice'] = $goodsPrice['reason'];

        return $price;
    }
}
