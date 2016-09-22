<?php


namespace DotPlant\Store\components\calculator;

use DotPlant\Store\helpers\ExtendedPriceHelper;
use DotPlant\Store\interfaces\NoGoodsCalculatorInterface;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\Module;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class CartCalculator implements NoGoodsCalculatorInterface
{

    /**
     * Calculates object price
     *
     * @param Cart $cart
     *
     * @return array
     */
    public static function getPrice($cart)
    {
        if ($cart instanceof Cart === false) {
            throw new InvalidParamException;
        }
        $price = [
            'totalPriceWithoutDiscount' => 0,
            'totalPriceWithDiscount' => 0,
            'items' => 0,
            'extendedPrice' => [],
            'isoCode' => $cart->currency_iso_code,
        ];
        // @todo split extended price by extended_price.target_class and check for final;
        $extendedPrices = ExtendedPriceHelper::getForObject($cart);

        $delivery = null;
        foreach ($cart->items as $item) {
            $item->calculate();
            $price['totalPriceWithDiscount'] += $item->total_price_with_discount;
            $price['totalPriceWithoutDiscount'] += $item->total_price_without_discount;
            if ($item->goods_id == 0) {
                $delivery = $item;
                continue; // It's a delivery. Do not count it as an order item
            }
            $price['items'] += Module::module()->countUniqueItemsOnly == 1 ? 1 : $item->quantity;
        }

        // other dependency as Delivery, Promocode etc

        $extPricesApplied = ExtendedPriceHelper::applyExtendedPrices(
            $extendedPrices,
            $price['totalPriceWithDiscount'],
            $cart->currency_iso_code
        );

        $price['totalPriceWithDiscount'] = $extendedPrices['priceAfter'];
        $price['extendedPrice'] = ArrayHelper::merge($price['extendedPrice'], $extPricesApplied['extendedPrice']);

        return $price;
    }
}
