<?php

namespace DotPlant\Store\controllers;

use app\vendor\dotplant\store\src\helpers\OrderHelper;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\helpers\BackendHelper;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use Yii;
use yii\base\UnknownMethodException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * OrderManageController implements the CRUD actions for Order model.
 */
class OrdersManageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'edit'],
                        'allow' => true,
                        'roles' => ['dotplant-store-order-view'],
                    ],
                    [
                        'actions' => ['remove-item', 'add-item', 'edit-items'],
                        'allow' => true,
                        'roles' => ['dotplant-store-order-edit'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['dotplant-store-order-delete'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ]
        ];
    }

    /**
     * Lists all Order models.
     * @return mixed
     */
    public function actionIndex($contextId = null)
    {
        $contextId = BackendHelper::getContext($contextId);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Order::find()->where(['context_id' => $contextId, 'is_deleted' => 0]),
            ]
        );
        return $this->render(
            'index',
            [
                'contextId' => $contextId,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing Order model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new Order;
            $model->loadDefaultValues();
            throw new OrderException(Yii::t('dotplant.store', 'Order creation via admin panel is not implemented yet'));
        } else {
            $model = $this->findModel($id);
        }
        $model->autoSaveProperties = true;
        $hasAccess = ($model->isNewRecord && Yii::$app->user->can('dotplant-store-order-create'))
            || (!$model->isNewRecord && Yii::$app->user->can('dotplant-store-order-edit'));
        $model->scenario = 'backend-order-updating';
        if ($model->load(Yii::$app->request->post())) {
            if (!$hasAccess) {
                throw new ForbiddenHttpException;
            }
            if ($model->save()) {
                return $this->redirect(['edit', 'id' => $model->id]);
            }
        }
        return $this->render(
            'edit',
            [
                'hasAccess' => $hasAccess,
                'model' => $model,
            ]
        );
    }


    /**
     * @param $order_id
     * @param $item_id
     * @param $returnUrl
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRemoveItem($order_id, $item_id, $returnUrl)
    {

        $order = $this->findModel($order_id);

        if (null !== $orderItem = OrderItem::findOne($item_id)) {
            /**
             * @var $orderItem OrderItem
             */
            if (OrderHelper::removeItem($order, $orderItem)) {
                return $this->redirect([$returnUrl]);
            }
            throw new UnknownMethodException('Order item has not removed');
        }
        throw new NotFoundHttpException('The requested page does not exist.');

    }


    /**
     * @param $order_id
     * @param bool|false $goods_id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAddItem($order_id, $goods_id = false)
    {

        $values = [];
        $goods = null;
        $prices = [];
        $order = $this->findModel($order_id);
        if ($goods_id !== false && ($goods = Goods::get($goods_id))) {
            $values[$goods->id] = $goods->name;
            $prices = GoodsWarehouse::find()
                ->indexBy('warehouse_id')
                ->where(['goods_id' => $goods->id])
                ->all();

            $request = Yii::$app->request;

            if ($request->isPost &&
                ($quantity = $request->post('quantity', false)) &&
                ($warehouse_id = $request->post('warehouse_id', false))
            ) {
                OrderHelper::addItem($order, $goods, $warehouse_id, $quantity);
                return $this->redirect(['/store/orders-manage/edit', 'id' => $order_id]);
            }


        }


        return $this->render('add-item', [
                'order_id' => $order_id,
                'values' => $values,
                'goods_id' => $goods_id,
                'goods' => $goods,
                'prices' => $prices
            ]
        );
    }


    /**
     * Deletes an existing Order model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'backend-order-soft-deleting';
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Finds the Order model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Order the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Order::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditItems($id)
    {
        $model = $this->findModel($id);
        $itemsIds = Yii::$app->request->post('id', []);
        $action = Yii::$app->request->post('action', false);
        if (empty($model) == false && empty($itemsIds) === false && empty($action) === false) {
            switch ($action) {
                case 'move_to_new_order':
                    OrderHelper::separate($model->id, $itemsIds);
                    break;
            }
        }
        return $this->redirect(['/store/orders-manage/edit', 'id' => $id]);
    }

}
