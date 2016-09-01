<?php

namespace DotPlant\Store\controllers;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use Yii;
use DotPlant\Store\models\Warehouse;
use yii\data\ActiveDataProvider;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WarehousesManageController implements the CRUD actions for Warehouse model.
 */
class WarehousesManageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Warehouse models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Warehouse::find(),
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
     * Updates an existing Warehouse model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new Warehouse;
            $model->loadDefaultValues();
        } else {
            $model = $this->findModel($id);
        }
        if ($model->load(Yii::$app->request->post())) {
            $error = false;
            foreach (Yii::$app->request->post('WarehouseTranslation') as $languageId => $attributes) {
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
     * Deletes an existing Warehouse model.
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
     * Finds the Warehouse model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Warehouse | MultilingualActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Warehouse::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
