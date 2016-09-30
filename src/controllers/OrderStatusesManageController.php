<?php

namespace DotPlant\Store\controllers;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\helpers\BackendHelper;
use DotPlant\Store\models\order\OrderStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * OrderStatusesManageController implements the CRUD actions for OrderStatus model.
 */
class OrderStatusesManageController extends Controller
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
                        'roles' => ['dotplant-store-order-status-view'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['dotplant-store-order-status-delete'],
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
     * Lists all OrderStatus models.
     * @return mixed
     */
    public function actionIndex($contextId = null)
    {
        $contextId = BackendHelper::getContext($contextId);
        $query = (new ActiveQuery(OrderStatus::class))
            ->innerJoinWith('smartTranslation')
            ->where(['context_id' => $contextId, 'is_deleted' => 0]);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'sort_order' => SORT_ASC,
                    ],
                ],
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
     * Updates an existing OrderStatus model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id = null, $contextId = null)
    {
        if ($id === null) {
            $model = new OrderStatus;
            $model->loadDefaultValues();
            $model->context_id = BackendHelper::getContext($contextId);
        } else {
            $model = $this->findModel($id);
        }
        $hasAccess = ($model->isNewRecord && Yii::$app->user->can('dotplant-store-order-status-create'))
            || (!$model->isNewRecord && Yii::$app->user->can('dotplant-store-order-status-edit'));
        if ($model->load(Yii::$app->request->post())) {
            if (!$hasAccess) {
                throw new ForbiddenHttpException;
            }
            $error = false;
            foreach (Yii::$app->request->post('OrderStatusTranslation') as $languageId => $attributes) {
                $model->translate($languageId)->setAttributes($attributes);
                if (!$model->translate($languageId)->validate()) {
                    $error = true;
                }
            }
            if (!$error) {
                $error = !$model->save();
            }
            if (!$error) {
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
     * Deletes an existing OrderStatus model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();
        return $this->redirect(['index', 'contextId' => $model->context_id]);
    }

    /**
     * Finds the OrderStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderStatus|MultilingualActiveRecord|MultilingualTrait the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = (new ActiveQuery(OrderStatus::class))->where(['id' => $id])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
