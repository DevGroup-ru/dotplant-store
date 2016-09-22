<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\actions\order\PaymentCheckAction;
use DotPlant\Store\actions\order\PaymentPayAction;
use DotPlant\Store\actions\order\PaymentSuccessAction;
use DotPlant\Store\actions\order\SingleStepOrderAction;

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
        return $this->render('list');
    }

    public function actionShow($hash)
    {
        return $this->render('show');
    }

    public function actionRefund($hash)
    {
        return $this->render('refund');
    }
}