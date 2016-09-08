<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\actions\order\SingleStepOrderAction;

class OrderController extends \yii\web\Controller
{
    public function actions()
    {
        return [
            'create' => [
                'class' => SingleStepOrderAction::class,
            ],
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

    public function actionPayment($hash)
    {
        return $this->render('payment');
    }

    public function actionShow($hash)
    {
        return $this->render('show');
    }

    public function actionSuccess($hash)
    {
        return $this->render('success');
    }
}
