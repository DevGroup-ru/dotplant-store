<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\actions\order\PaymentCheckAction;
use DotPlant\Store\actions\order\PaymentPayAction;
use DotPlant\Store\actions\order\PaymentSuccessAction;
use DotPlant\Store\actions\order\SingleStepOrderAction;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\Payment;
use DotPlant\Store\components\Store;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use yii\data\ActiveDataProvider;

class OrderController extends \yii\web\Controller
{
    public function actions()
    {
        return [
            'create' => [
                'class' => SingleStepOrderAction::class,
            ],
            'payment' => PaymentPayAction::class,
            'check' => PaymentCheckAction::class,
            'success' => PaymentSuccessAction::class,
        ];
    }

    public function actionError($hash)
    {
        return $this->render('error');
    }

    public function actionList()
    {
        $orders = Store::getOrders(\Yii::$app->user->id);
        return $this->render('list', ['orders' => $orders]);
    }

    public function actionShow($hash)
    {
        $order = Store::getOrder($hash);
        $orderDeliveryInformation = OrderDeliveryInformation::findOne(['order_id' => $order->id]);
        $payment = Payment::findOne($order->id);
        return $this->render('show', ['order' => $order, 'orderDeliveryInformation' => $orderDeliveryInformation]);
    }

    public function actionRefund($hash)
    {
        return $this->render('refund');
    }
}