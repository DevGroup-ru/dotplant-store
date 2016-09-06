<?php

namespace DotPlant\Store\controllers;

use DotPlant\Store\components\Order;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class CartController
 *
 * @todo: Add a parameters check for all actions
 *
 * @package DotPlant\Store\controllers
 */
class CartController extends \yii\web\Controller
{
    private function result($data)
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $data;
        } else {
            Yii::$app->session->setFlash('error', $data['errorMessage']);
            return $this->redirect(Yii::$app->request->referrer);
        }
    }

    private function getRequiredPostParam($paramName)
    {
        $value = Yii::$app->request->post($paramName);
        if ($value === null) {
            throw new BadRequestHttpException;
        }
        return $value;
    }

    public function actionAdd()
    {
        $goodsId = $this->getRequiredPostParam('goodsId');
        $result = [];
        $quantity = Yii::$app->request->post('quantity', 1);
        $warehouseId = Yii::$app->request->post('warehouseId'); // This parameter is not required.
        try {
            $model = Order::getCart();
            $model->addItem($goodsId, $quantity, $warehouseId);
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }

    public function actionChangeQuantity()
    {
        $itemId = $this->getRequiredPostParam('id');
        $quantity = $this->getRequiredPostParam('quantity');
        $result = [];
        try {
            $model = Order::getCart();
            $model->changeItemQuantity($itemId, $quantity);
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }

    public function actionClear()
    {
        $model = Order::getCart(false);
        if ($model !== null) {
            $model->clear();
        }
        Yii::$app->session->setFlash('success', Yii::t('dotplant.store', 'Cart has been cleared'));
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionIndex()
    {
        $model = Order::getCart(false);
        return $this->render('index', ['model' => $model]);
    }

    public function actionRemove()
    {
        $itemId = $this->getRequiredPostParam('id');
        $result = [];
        try {
            $model = Order::getCart();
            $model->removeItem($itemId);
            $result['successMessage'] = Yii::t('dotplant.store', 'Item has been removed');
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }
}
