<?php

namespace DotPlant\Store\handlers;

use DevGroup\Users\helpers\ModelMapHelper;
use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Emails\helpers\EmailHelper;
use DotPlant\Store\components\Store;
use DotPlant\Store\events\AfterOrderManagerChangeEvent;
use DotPlant\Store\events\AfterOrderStatusChangeEvent;
use DotPlant\Store\helpers\BackendHelper;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderStatus;
use DotPlant\Store\Module;

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
     * @param AfterOrderStatusChangeEvent $event
     */
    public static function attachRandomManagerToNewOrder(AfterOrderStatusChangeEvent $event)
    {
        $order = Order::findOne($event->orderId);
        if ($event->statusId == Store::getPaidOrderStatusId($order->context_id) && $order->manager_id == 0) {
            $managerId = array_rand(BackendHelper::managersDropDownList());
            $order->attachManager($managerId);
        }
    }

    /**
     * You should set `emailTemplateId` via params. It is a template id from Template model of `dotplant/email` extension
     * @param AfterOrderManagerChangeEvent $event
     */
    public static function sendEmailToNewManager(AfterOrderManagerChangeEvent $event)
    {
        if (isset($event->data['emailTemplateId'])) {
            $managerEmail = call_user_func([ModelMapHelper::User()['class'], 'find'])
                ->select(['email'])
                ->where(['id' => $event->managerId])
                ->scalar();
            if (!empty($managerEmail)) {
                EmailHelper::sendNewMessage(
                    $managerEmail,
                    $event->data['emailTemplateId'],
                    [
                        'managers' => BackendHelper::managersDropDownList(),
                        'managerId' => $event->managerId,
                        'oldManagerId' => $event->oldManagerId,
                        'orderId' => $event->orderId,
                    ]
                );
            }
        }
    }

    /**
     * You should set `emailTemplateId` via params. It is a template id from Template model of `dotplant/email` extension
     * @param AfterOrderStatusChangeEvent $event
     */
    public static function sendEmailToCustomerAboutStatusChanging(AfterOrderStatusChangeEvent $event)
    {
        $statuses = OrderStatus::listData();
        $order = Order::findOne($event->orderId);
        if (
            isset(
                $statuses[$event->oldStatusId],
                $statuses[$event->statusId],
                $event->data['emailTemplateId']
            )
            && !empty($order->deliveryInformation)
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

    /**
     * You should set `emailTemplateId` via params. It is a template id from Template model of `dotplant/email` extension
     * @param AfterOrderStatusChangeEvent $event
     */
    public static function sendEmailToCustomerAboutNewOrder(AfterOrderStatusChangeEvent $event)
    {
        $order = Order::findOne($event->orderId);
        if (
            $event->statusId == Store::getPaidOrderStatusId($order->context_id)
            && isset($event->data['emailTemplateId'])
            && !empty($order->deliveryInformation)
        ) {
            sleep(5);
            EmailHelper::sendNewMessage(
                $order->deliveryInformation->email,
                $event->data['emailTemplateId'],
                [
                    'orderId' => $event->orderId,
                ]
            );
        }
    }
}
