<?php

namespace DotPlant\Store\handlers;

use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Store\events\OrderAfterStatusChangeEvent;
use DotPlant\Store\models\order\OrderStatus;

class OrderHandler
{
    public static function afterUserCurrencyChange(AfterUserCurrencyChangeEvent $event)
    {
        /**
         * @todo: recalculate cart if user changes a currency
         */
    }

    public static function afterUserLogin()
    {
        /**
         * @todo: set created_by for cart model after login
         * @todo: set created_by for order model after login
         */
    }

    public static function sendEmailToCustomerAboutStatusChanging(OrderAfterStatusChangeEvent $event)
    {
        $statuses = OrderStatus::listData();
        if (isset($statuses[$event->oldStatusId], $statuses[$event->statusId])) {
            // add task to send message
            echo "Order status has been changed from {$statuses[$event->oldStatusId]} to {$statuses[$event->statusId]}";
            die;
        }
    }
}
