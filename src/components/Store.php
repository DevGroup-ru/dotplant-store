<?php

namespace DotPlant\Store\components;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\events\RetailCheckEvent;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use DotPlant\Store\Module;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Order
 *
 * @package DotPlant\Store\components
 */
class Store
{
    const CART_SESSION_KEY = 'DotPlant:Store:CartId';
    const ORDER_HASHES_SESSION_KEY = 'DotPlant:Store:OrderHashes';

    protected static function createCart()
    {
        $model = new Cart;
        $model->loadDefaultValues();
        $model->context_id = Yii::$app->multilingual->context_id;
        $model->created_by = Yii::$app->user->id;
        $model->currency_iso_code = CurrencyHelper::getUserCurrency()->iso_code;
        $model->is_retail = (int) static::isRetail();
        if (!$model->save()) {
            throw new OrderException(Yii::t('dotplant.store', 'Can not create a new cart'));
        }
        Yii::$app->session->set(self::CART_SESSION_KEY, $model->id);
        return $model;
    }

    /**
     * Get cart instance
     * @param bool $createIfNotExists
     * @return Cart|null
     */
    public static function getCart($createIfNotExists = true)
    {
        $id = Yii::$app->session->get(self::CART_SESSION_KEY);
        $userId = Yii::$app->user->id;
        $model = null;
        if ($userId !== null) {
            $model = Cart::findOne(
                [
                    'context_id' => Yii::$app->multilingual->context_id,
                    'created_by' => $userId,
                ]
            );
        }
        if ($model === null && $id !== null) {
            $model = Cart::findOne(
                [
                    'id' => $id,
                    'context_id' => Yii::$app->multilingual->context_id,
                ]
            );
        }
        if ($model === null && $createIfNotExists) {
            $model = static::createCart();
        }
        return $model;
    }

    /**
     * Get order
     * @param string $hash
     * @return \DotPlant\Store\models\order\Order
     */
    public static function getOrder($hash)
    {
        $model = Order::findOne(
            [
                'hash' => $hash,
                'context_id' => Yii::$app->multilingual->context_id,
            ]
        );
        return $model;
    }

    /**
     * @param Cart $cart
     * @return \DotPlant\Store\models\order\Order|null
     * @throws OrderException
     */
    public static function createOrder($cart)
    {
        if ($cart === null || $cart->items_count == 0) {
            throw new OrderException(Yii::t('dotplant.store', 'Can not create an order. Cart is empty.'));
        }
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // check items quantity and set warehouses
            $cart->prepare();
            // reserve items
            $cart->reserve();
            $cart->calculate();
            // lock cart
            $cart->is_locked = 1;
            $cart->save(true, ['is_locked', 'items_count', 'total_price_with_discount', 'total_price_without_discount']);
            // save order
            $orderCurrency = CurrencyHelper::findCurrencyByIso($cart->currency_iso_code);
            $mainCurrency = CurrencyHelper::getMainCurrency();
            $order = new Order;
            $order->scenario = 'order-creation';
            $order->attributes = [
                'context_id' => $cart->context_id,
                'currency_iso_code' => $cart->currency_iso_code,
                'status_id' => static::getNewOrderStatusId($cart->context_id),
                'is_retail' => $cart->is_retail,
                'items_count' => $cart->items_count,
                'total_price_with_discount' => $cart->total_price_with_discount,
                'total_price_without_discount' => $cart->total_price_without_discount,
                'rate_to_main_currency' => ($orderCurrency->convert_rate * $orderCurrency->convert_nominal)
                    / ($mainCurrency->convert_rate * $mainCurrency->convert_nominal),
            ];
            if (!$order->save()) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not save a new order'));
            }
            // set order_id for order_items
            if (OrderItem::updateAll(['order_id' => $order->id], ['cart_id' => $cart->id]) != count($cart->items)) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not update order items'));
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return null;
        }
        // set hash to session
        Yii::$app->session->set(
            self::ORDER_HASHES_SESSION_KEY,
            ArrayHelper::merge(Yii::$app->session->get(self::ORDER_HASHES_SESSION_KEY, []), [$order->hash])
        );
        return $order;
    }

    /**
     * @param Order $order
     * @return bool
     */
    public static function markOrderAsPaid($order)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!isset($order->items[0]->cart)) {
                throw new OrderException(Yii::t('dotplant.store', 'Cart not found'));
            }
            $cart = $order->items[0]->cart;
            // check status
            // remove cart_id from items
            if (OrderItem::updateAll(['cart_id' => null], ['order_id' => $order->id]) != count($order->items)) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not update one or more order items'));
            }
            // unlock cart
            $cart->is_locked = 0;
            $cart->total_price_with_discount = 0;
            $cart->total_price_without_discount = 0;
            $cart->items_count = 0;
            if (!$cart->save()) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not unlock the cart'));
            }
            // change order status
            $order->scenario = 'status-changing';
            $order->status_id = static::getPaidOrderStatusId($order->context_id);
            if (!$order->save()) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not update the order'));
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
        return true;
    }

    /**
     * Whether to show a retail price
     * @return bool true - retail, false - wholesale
     */
    public static function isRetail()
    {
        $event = new RetailCheckEvent;
        Module::module()->trigger(Module::EVENT_RETAIL_CHECK, $event);
        return $event->isRetail;
    }

    /**
     * @param int|null $contextId
     * @return int|null
     */
    public static function getNewOrderStatusId($contextId = null)
    {
        return static::getStatusId('newOrderStatusId', $contextId);
    }

    /**
     * @param int|null $contextId
     * @return int|null
     */
    public static function getPaidOrderStatusId($contextId = null)
    {
        return static::getStatusId('paidOrderStatusId', $contextId);
    }

    /**
     * @param int|null $contextId
     * @return int|null
     */
    public static function getDoneOrderStatusId($contextId = null)
    {
        return static::getStatusId('doneOrderStatusId', $contextId);
    }

    /**
     * @param int|null $contextId
     * @return int|null
     */
    public static function getCanceledOrderStatusId($contextId = null)
    {
        return static::getStatusId('canceledOrderStatusId', $contextId);
    }

    protected static function getStatusId($type, $contextId = null)
    {
        if ($contextId === null) {
            $contextId = Yii::$app->multilingual->context_id;
        }
        $statusesList = Module::module()->$type;
        return isset($statusesList[$contextId]) ? $statusesList[$contextId] : null;
    }
}
