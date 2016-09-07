<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\components\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;

class OrderController extends \yii\web\Controller
{
    public function actionCreate($hash = null)
    {
        $cart = Order::getCart(false);
        if ($cart === null || $cart->items_count == 0) {
            \Yii::$app->session->setFlash('error', \Yii::t('dotplant.store', 'Cart is empty'));
            return $this->redirect(['/store/cart']);
        }
        $model = new OrderDeliveryInformation;
        $model->loadDefaultValues();
        $model->context_id = \Yii::$app->multilingual->context_id;
        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            // is it a new order?
            $order = Order::createOrder($cart);
            if ($order !== null) {
                $model->order_id = $order->id;
                $model->save();
                return $this->redirect(['create', 'hash' => $order->hash]);
            }
        }
        return $this->render('create', ['model' => $model]);
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
