<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\components\Order;
use Yii;

class CartController extends \yii\web\Controller
{
    public function actionAdd()
    {
        $goodsId = Yii::$app->request->post('goodsId');
        $quantity = Yii::$app->request->post('quantity');
        $warehouseId = Yii::$app->request->post('warehouseId');
        try {
            $model = Order::getCart();
            $model->addItem($goodsId, $quantity, $warehouseId);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionChangeQuantity()
    {
        $itemId = Yii::$app->request->post('itemId');
        $quantity = Yii::$app->request->post('quantity');
        try {
            $model = Order::getCart();
            $model->changeItemQuantity($itemId, $quantity);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionClear()
    {
        $model = Order::getCart();
        $model->clear();
        Yii::$app->session->setFlash('success', Yii::t('dotlant.store', 'Cart has been cleared'));
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionIndex()
    {
        $model = Order::getCart(false);
        return $this->render('index', ['model' => $model]);
    }

    public function actionRemove()
    {
        $itemId = Yii::$app->request->post('itemId');
        try {
            $model = Order::getCart();
            $model->removeItem($itemId);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }
        Yii::$app->session->setFlash('success', Yii::t('dotlant.store', 'Item has been removed'));
        return $this->redirect(Yii::$app->request->referrer);
    }
}
