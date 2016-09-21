<?php

namespace DotPlant\Store\controllers;


use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\models\vendor\Vendor;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Yii;

class VendorsManageController extends Controller
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
                        'roles' => ['dotplant-store-vendor-view'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['dotplant-store-vendor-delete'],
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
     * Lists all Payment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider(
            [
                'query' => Vendor::find(),
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
     * Updates an existing Payment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionEdit($id = null)
    {
        if ($id === null) {
            $model = new Vendor();
            $model->loadDefaultValues();
        } else {
            if (null === $model = Vendor::findOne($id)) {
                throw new NotFoundHttpException(
                    Yii::t(
                        'dotplant.store',
                        '{model} with id #{id} not found',
                        ['model' => Yii::t('dotplant.store', 'Vendor'), 'id' => $id]
                    )
                );
            }
        }
        $hasAccess = ($model->isNewRecord && Yii::$app->user->can('dotplant-store-vendor-create'))
            || (!$model->isNewRecord && Yii::$app->user->can('dotplant-store-vendor-edit'));
        /** @var Vendor | MultilingualTrait | MultilingualActiveRecord $model */
        if ($model->load(Yii::$app->request->post())) {
            if (!$hasAccess) {
                throw new ForbiddenHttpException;
            }
            $success = true;
            foreach (Yii::$app->request->post('VendorTranslation') as $languageId => $attributes) {
                $model->translate($languageId)->setAttributes($attributes);
                $success = $model->translate($languageId)->validate() && $success;
            }
            $success = $model->save() && $success;
            if (true === $success) {
                Yii::$app->session->setFlash(
                    'success',
                    Yii::t(
                        'dotplant.store',
                        '{model} successfully saved',
                        ['model' => Yii::t('dotplant.store', 'Vendor')]
                    )
                );
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
     * Deletes an existing Payment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        if (null !== $vendor = Vendor::findOne($id)) {
            $vendor->delete();
        } else {
            throw new NotFoundHttpException(
                Yii::t(
                    'dotplant.store',
                    '{model} with id #{id} not found',
                    ['model' => Yii::t('dotplant.store', 'Vendor'), 'id' => $id]
                )
            );
        }
        return $this->redirect(['index']);
    }
}