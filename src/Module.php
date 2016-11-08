<?php

namespace DotPlant\Store;

/**
 * Class Module
 *
 * @todo: Add scenarios for OrderDeliveryInformation
 *
 * @package DotPlant\Store
 */
class Module extends \yii\base\Module
{
    const EVENT_AFTER_ORDER_STATUS_CHANGE = 'dotplant.store.afterOrderStatusChange';
    const EVENT_AFTER_ORDER_MANAGER_CHANGE = 'dotplant.store.afterOrderManagerChange';
    const EVENT_AFTER_USER_REGISTERED = 'dotplant.store.afterUserRegistered';
    const EVENT_RETAIL_CHECK = 'dotplant.store.retailCheck';
    const EVENT_PAYMENT_STATUS_SUCCESS = 'dotplant.store.paymentStatusSuccess';
    const EVENT_PAYMENT_STATUS_ERROR = 'dotplant.store.paymentStatusError';
    const EVENT_PAYMENT_STATUS_FORMED = 'dotplant.store.paymentStatusFormed';
    const EVENT_PAYMENT_STATUS_PROCESSED = 'dotplant.store.paymentStatusProcessed';

    // cart
    public $allowToAddSameGoods = 0;
    public $countUniqueItemsOnly = 0;
    public $singlePriceForWarehouses = 0;
    public $registerGuestInCart = 1;
    public $deliveryFromWarehouse = 0;
    public $allowOrderOutOfStock = 0;

    // order statuses
    public $newOrderStatusId = [];
    public $paidOrderStatusId = [];
    public $doneOrderStatusId = [];
    public $canceledOrderStatusId = [];




    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        $module = \Yii::$app->getModule('store');
        if ($module === null) {
            $module = \Yii::createObject(self::class, ['store']);
        }
        return $module;
    }
}
