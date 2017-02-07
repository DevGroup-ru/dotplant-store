<?php

namespace app\vendor\dotplant\store\src\helpers;


use DotPlant\Store\components\calculator\CartCalculator;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use Yii;

class OrderHelper
{


    public static function addItem()
    {

    }


    public static function removeItem(Order $order, OrderItem $item)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {

            self::reCalculateOrder($order);

            $item->delete();

            $order->cart->calculate();
            $order->cart->save();
            $order->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }


    public static function reCalculateOrder(Order $order)
    {
        $cart = new Cart();
        $cart->setAttributes(
            $order->getAttributes(
                [
                    'context_id',
                    'currency_iso_code',
                    'is_retail',
                    'items_count',
                    'total_price_with_discount',
                    'total_price_without_discount',
                    'user_id',
                ]
            )
        );
        $cart->load($order->getAttributes());
        $items = $order->items;
        foreach ($items as &$item) {
            $item->populateRelation('cart', $cart);
        }
        $cart->populateRelation('items', $items);
        if($cart->calculate()) {
           $dd = 1;
        }



    }


}