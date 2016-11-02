<?php

namespace DotPlant\Store\controllers;

use DevGroup\Frontend\controllers\FrontendController;
use DevGroup\Frontend\Universal\SuperAction;
use DotPlant\Monster\models\ServiceEntity;
use DotPlant\Monster\Universal\ServiceMonsterAction;
use DotPlant\Store\components\CartProvider;
use DotPlant\Store\components\Store;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Class CartController
 *
 * @package DotPlant\Store\controllers
 */
class CartController extends FrontendController
{

    public function actions()
    {
        return [
            'index' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'cart',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = CartProvider::class;
                        },
                    ],
                ],
            ],
        ];
    }

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
        $result = [
            'isSuccess' => false,
        ];
        $quantity = Yii::$app->request->post('quantity', 1);
        $warehouseId = Yii::$app->request->post('warehouseId'); // This parameter is not required.
        try {
            $model = Store::getCart();
            $model->addItem($goodsId, $quantity, $warehouseId);
            $item = null;
            foreach ($model->items as $singleItem) {
                if ($singleItem->goods_id == $goodsId) {
                    $item = $singleItem;
                    break;
                }
            }
            $result = [
                'isSuccess' => true,
                'itemsCount' => $model->items_count,
                'totalPrice' => $model->total_price_with_discount,
                'totalPriceWithoutDiscount' => $model->total_price_without_discount,
                'quantity' => $item->quantity,
                'itemPrice' => $item->total_price_with_discount,
                'itemPriceWithoutDiscount' => $item->total_price_without_discount,
            ];
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }

    public function actionChangeQuantity()
    {
        $itemId = $this->getRequiredPostParam('id');
        $quantity = $this->getRequiredPostParam('quantity');
        $result = [
            'isSuccess' => false,
        ];
        try {
            $model = Store::getCart();
            $model->changeItemQuantity($itemId, $quantity);
            $item = $model->items[$itemId];
            $result = [
                'isSuccess' => true,
                'itemsCount' => $model->items_count,
                'totalPrice' => $model->total_price_with_discount,
                'totalPriceWithoutDiscount' => $model->total_price_without_discount,
                'quantity' => $item->quantity,
                'itemPrice' => $item->total_price_with_discount,
                'itemPriceWithoutDiscount' => $item->total_price_without_discount,
            ];
        } catch (\Exception $e) {
            $result['isSuccess'] = false;
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }

    public function actionClear()
    {
        $model = Store::getCart(false);
        if ($model !== null) {
            $model->clear();
        }
        Yii::$app->session->setFlash('success', Yii::t('dotplant.store', 'Cart has been cleared'));
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionRemove()
    {
        $itemId = $this->getRequiredPostParam('id');
        $result = [
            'isSuccess' => false,
        ];
        try {
            $model = Store::getCart();
            $model->removeItem($itemId);
            $result = [
                'isSuccess' => true,
                'itemsCount' => $model->items_count,
                'totalPrice' => $model->total_price_with_discount,
                'totalPriceWithoutDiscount' => $model->total_price_without_discount,
                'successMessage' => Yii::t('dotplant.store', 'Item has been removed'),
            ];
        } catch (\Exception $e) {
            $result['errorMessage'] = $e->getMessage();
        }
        return $this->result($result);
    }
}
