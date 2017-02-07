<?php

namespace DotPlant\Store\controllers;

use app\vendor\dotplant\store\src\helpers\OrderHelper;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\helpers\BackendHelper;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
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
                        'actions' => ['remove-item', 'add-item'],
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


    public function actionRemoveItem($order_id, $item_id, $returnUrl)
    {

        $order = $this->findModel($order_id);

        if (null !== $orderItem = OrderItem::findOne($item_id)) {
            /**
             * @var $orderItem OrderItem
             */
            if (OrderHelper::removeItem($order, $orderItem)) {
                $this->redirect([$returnUrl]);
            }
            throw new UnknownMethodException('Order item has not removed');
        }
        throw new NotFoundHttpException('The requested page does not exist.');

    }


    public function actionAddItem()
    {

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
}
