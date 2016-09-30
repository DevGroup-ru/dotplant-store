<?php

namespace DotPlant\Store\controllers;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DotPlant\Store\models\warehouse\Warehouse;
use Yii;
use yii\base\InvalidParamException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
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
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'edit'],
                        'allow' => true,
                        'roles' => ['dotplant-store-warehouse-view'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['dotplant-store-warehouse-delete'],
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
     * Lists all Warehouse models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Warehouse::find(),
                'sort' => [
                    'defaultOrder' => [
                        'priority' => SORT_ASC,
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
        $hasAccess = ($model->isNewRecord && Yii::$app->user->can('dotplant-store-warehouse-create'))
            || (!$model->isNewRecord && Yii::$app->user->can('dotplant-store-warehouse-edit'));
        if ($model->load(Yii::$app->request->post())) {
            if (!$hasAccess) {
                throw new ForbiddenHttpException;
            }
            $error = false;
            try {
                $model->params = Json::decode($model->packed_json_params);
            } catch (InvalidParamException $e) {
                $model->addError('packed_json_params', 'Syntax error.');
                $error = true;
            }
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
                'hasAccess' => $hasAccess,
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
