<?php

namespace DotPlant\Store\controllers;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use Yii;
use DotPlant\Store\models\order\Delivery;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DeliveriesManageController implements the CRUD actions for Delivery model.
 */
class DeliveriesManageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return []; // @todo: add permissions
    }

    /**
     * Lists all Delivery models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Delivery::find(),
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
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing Delivery model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new Delivery;
            $model->loadDefaultValues();
        } else {
            $model = $this->findModel($id);
        }
        if ($model->load(Yii::$app->request->post())) {
            $error = false;
            foreach (Yii::$app->request->post('DeliveryTranslation') as $languageId => $attributes) {
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
     * Deletes an existing Delivery model.
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
     * Finds the Delivery model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Delivery|MultilingualActiveRecord|MultilingualTrait the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Delivery::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
