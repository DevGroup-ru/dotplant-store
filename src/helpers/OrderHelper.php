<?php

namespace app\vendor\dotplant\store\src\helpers;


use DotPlant\Store\components\calculator\CartCalculator;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use Yii;

/**
 * Class OrderHelper
 * @package app\vendor\dotplant\store\src\helpers
 */
class OrderHelper
{


    /**
     * @param Order $order
     * @param Goods $goods
     * @param $warehouse_id
     * @param int $quantity
     * @param bool $changed_by_manager
     * @return bool
     */
    public static function addItem(Order $order, Goods $goods, $warehouse_id, $quantity = 1, $changed_by_manager = false)
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

        $orderItem = new OrderItem([
            'order_id' => $order->id,
            'goods_id' => $goods->id,
            'warehouse_id' => $warehouse_id,
            'quantity' => $quantity,
            'changed_by_manager' => $changed_by_manager
        ]);

        $orderItem->populateRelation('cart', $cart);

        $orderItem->calculate();
        return $orderItem->save() && self::reCalculate($order);


    }


    /**
     * @param Order $order
     * @param OrderItem $item
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function removeItem(Order $order, OrderItem $item)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $item->delete();
            $order->setScenario('backend-order-updating');
            $order->getItems()->all();
            self::reCalculate($order);
            $order->save();
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }


    /**
     * @param Order $order
     * @return Order
     */
    private static function reCalculate(Order &$order)
    {
        $items = $order->getItems()->all();;
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
        foreach ($items as &$item) {
            $item->populateRelation('cart', $cart);
        }
        $cart->populateRelation('items', $items);
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
            $newOrder->created_at = time();
            $newOrder->updated_at = $order->updated_at;
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
            {
                Yii::warning(implode(', ', $newOrder->firstErrors));
            }
            static::reCalculate($order);
        }
        return $result;
    }
}