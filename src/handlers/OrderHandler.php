<?php

namespace DotPlant\Store\handlers;

use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Emails\helpers\EmailHelper;
use DotPlant\Store\events\AfterOrderStatusChangeEvent;
use DotPlant\Store\models\order\Order;
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

    /**
     * You should set `emailTemplateId` via params. It is a template id from Template model of `dotplant/email` extension
     * @param AfterOrderStatusChangeEvent $event
     */
    public static function sendEmailToCustomerAboutStatusChanging(AfterOrderStatusChangeEvent $event)
    {
        $statuses = OrderStatus::listData();
        $order = Order::findOne($event->orderId);
        if (isset(
            $statuses[$event->oldStatusId],
            $statuses[$event->statusId],
            $event->data['emailTemplateId'],
            $order->deliveryInformation
        )
        ) {
            EmailHelper::sendNewMessage(
                $order->deliveryInformation->email,
                $event->data['emailTemplateId'],
                [
                    'orderId' => $event->orderId,
                    'oldStatusId' => $event->oldStatusId,
                    'statusId' => $event->statusId,
                    'statuses' => $statuses,
                    'userId' => \Yii::$app->user->id,
                ]
            );
        }
    }
}
