<?php

namespace DotPlant\Store\controllers;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\models\Context;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\components\MultilingualDataProviderQuery;
use DotPlant\Store\models\order\OrderStatus;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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
        return []; // @todo: add permissions
    }

    /**
     * Lists all OrderStatus models.
     * @return mixed
     */
    public function actionIndex($contextId = null)
    {
        $contexts = Context::find()->all();
        if ($contextId === null && count($contexts) > 0) {
            $contextId = reset($contexts)->id;
        }
        $tabs = [];
        foreach ($contexts as $context) {
            $tabs[] = [
                'active' => $context->id == $contextId,
                'label' => $context->name,
                'url' => ['index', 'contextId' => $context->id],
            ];
        }
        $query = (new ActiveQuery(OrderStatus::class))
            ->innerJoinWith('smartTranslation')
            ->where(['context_id' => $contextId]);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );
        return $this->render(
            'index',
            [
                'contextId' => $contextId,
                'dataProvider' => $dataProvider,
                'tabs' => $tabs,
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
            if ($contextId === null) {
                $context = Context::find()->one();
                if ($context === null) {
                    throw new BadRequestHttpException(Yii::t('dotplant.store', 'Context not found'));
                }
                $contextId = $context->id;
            }
            $model->context_id = $contextId;
        } else {
            $model = $this->findModel($id);
        }
        if ($model->load(Yii::$app->request->post())) {
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
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
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
        $query = (new ActiveQuery(OrderStatus::class))->where(['id' => $id]);
        if (($model = $query->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
