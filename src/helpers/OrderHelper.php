<?php

namespace DotPlant\Store\helpers;

use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;

/**
 * Class OrderHelper
 * @package DotPlant\Store\helpers
 */
class OrderHelper
{
    /**
     * @param Order $order
     * @return Order
     */
    private static function reCalculate(Order &$order)
    {
        $cart = new Cart();
        $cart->populateRelation('items', $order->items);
        $cart->calculate();

        $order->updateAttributes($cart->getAttributes(
            [
                'items_count',
                'total_price_with_discount',
                'total_price_without_discount',
            ]
        ));
        return $order;
    }


    /**
     * @param $orderId
     * @param $itemsIds
     * @return bool|int new Order ID
     */
    public static function separate($orderId, $itemsIds)
    {
        $result = false;
        $order = Order::findOne($orderId);
        $order->scenario = 'order-creation';

        /** @var OrderItem[] $orderItems * */
        $orderItems = $order->getItems()->indexBy('id')->all();

        $targetItems = [];
        $deliveryItem = null;

        foreach ($orderItems as $itemId => $item) {
            if ($item->isDelivery() === false && in_array($itemId, $itemsIds)) {
                $targetItems[$itemId] = $item;
            } elseif ($item->isDelivery() === true) {
                $deliveryItem = $item;
            }
        }

        if (empty($targetItems) === false) {
            /**@var $newOrder Order */
            $newOrder = clone $order;
            $newOrder->id = null;
            $newOrder->created_by = \Yii::$app->user->id;
            $newOrder->updated_by = $newOrder->created_by;
            $newOrder->created_at = date("Y-m-d H:i:s");
            $newOrder->updated_at = $newOrder->created_at;
            $newOrder->isNewRecord = true;

            if ($newOrder->save()) {
                if ($deliveryItem !== null) {
                    $deliveryItem->id = null;
                    $deliveryItem->order_id = $newOrder->id;
                    $deliveryItem->isNewRecord = true;
                    $deliveryItem->save();
                }

                if ($deliveryInformation = $order->deliveryInformation) {
                    $deliveryInformation->id = null;
                    $deliveryInformation->order_id = $newOrder->id;
                    $deliveryInformation->isNewRecord = true;
                    $deliveryInformation->save();
                }

                /* @var OrderItem[] $targetItems */
                foreach ($targetItems as $item) {
                    $item->updateAttributes(['order_id' => $newOrder->id]);
                }
                static::reCalculate($newOrder);
                $result = $newOrder->id;
            }

            static::reCalculate($order);
        }


        return $result;
    }
}
