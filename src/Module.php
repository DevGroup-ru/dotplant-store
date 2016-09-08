<?php

namespace DotPlant\Store;

/**
 * Class Module
 *
 * @todo: Add scenarios for Order model
 * @todo: Add scenarios for OrderDeliveryInformation
 *
 * @package DotPlant\Store
 */
class Module extends \yii\base\Module
{
    const EVENT_ORDER_AFTER_STATUS_CHANGE = 'dotplant.store.orderAfterStatusChange';

    // cart
    public $allowToAddSameGoods = 0;
    public $countUniqueItemsOnly = 0;
    public $singlePriceForWarehouses = 0;
    public $registerGuestInCart = 1;
    // order statuses
    public $newOrderStatusId;
    public $paidOrderStatusId;
    public $doneOrderStatusId;
    public $canceledOrderStatusId;

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
