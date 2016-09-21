<?php

namespace DotPlant\Store\controllers;


use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\AdminUtils\traits\BackendRedirect;
use DotPlant\Store\actions\extendedPrice\ExtendedPriceAjaxFormAction;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceHandler;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Yii;

class ExtendedPriceManageController extends BaseController
{

    use BackendRedirect;

    public function actions()
    {
        return ['extended-price-entity' => ExtendedPriceAjaxFormAction::class];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'edit'],
                        'allow' => true,
                        'roles' => ['dotplant-store-extended-price-view'],
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'roles' => ['dotplant-store-extended-price-delete'],
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

    public function actionIndex()
    {
        $searchModel = new ExtendedPrice();
        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }


    public function actionEdit($id = false)
    {
        /** @var ExtendedPrice $model */
        $model = ExtendedPrice::loadModel(
            $id,
            true,
            false,
            86400,
            new NotFoundHttpException(
                Yii::t('app', "{model} with id :'{id}' not found!", [
                    'model' => Yii::t('app', 'ExtendedPrice'),
                    'id' => $id
                ])
            )
        );


        $model->loadDefaultValues();

        $params = [];

        $isLoaded = $model->load(\Yii::$app->request->post());
        $hasAccess = ($model->isNewRecord && Yii::$app->user->can('dotplant-store-extended-price-create'))
            || (!$model->isNewRecord && Yii::$app->user->can('dotplant-store-extended-price-edit'));
        if ($isLoaded && $hasAccess === false) {
            throw new ForbiddenHttpException;
        }
        if ($isLoaded) {
            if ($model->save()) {
                $this->redirectUser(
                    $model->id,
                    true,
                    ['/store/extended-price-manage/index'],
                    ['/store/extended-price-manage/edit', 'id' => $model->id]
                );
            }
        }

        if ($model->isNewRecord === false) {
            $newExtendedRule = new ExtendedPriceRule([
                'extended_price_id' => $model->id
            ]);
            $newExtendedRule->formName = 'NewExtendedPrice';
            $newExtendedRule->loadDefaultValues();

            $params['newExtendedRule'] = $newExtendedRule;

            $params['handlers'] = ExtendedPriceHandler::find()
                ->indexBy('id')
                ->select(['name', 'id'])
                ->where(['target_class' => ['all', $model->target_class]])
                ->column();

            $params['extendedPriceRules'] = $model->getExtendedPriceRules()->indexBy('id')->all();

            if (\Yii::$app->request->isPost) {
                if ($newExtendedRule->load(Yii::$app->request->post())) {
                    if ($newExtendedRule->save()) {
                        $this->refresh();
                    }
                }

                if (Model::loadMultiple(
                        $params['extendedPriceRules'],
                        Yii::$app->request->post()
                    ) && Model::validateMultiple($params['extendedPriceRules'])
                ) {
                    foreach ($params['extendedPriceRules'] as $rule) {
                        $handlerClass = $rule->extendedPriceHandler->handler_class;
                        /** @var Model $handlerModel */
                        $handlerModel = new $handlerClass($rule->params);
                        if ($handlerModel->validate()) {
                            $rule->save();
                        }
                    }

                    $this->refresh();
                }
            }
        }
        $params['model'] = $model;
        return $this->render('edit', $params);
    }


    public function actionDelete($id, $returnUrl)
    {
        /** @var ExtendedPrice $model */
        $model = ExtendedPrice::loadModel(
            $id,
            true,
            false,
            86400,
            new NotFoundHttpException(
                Yii::t('app', "{model} with id :'{id}' not found!", [
                    'model' => Yii::t('app', 'Extended Price'),
                    'id' => $id
                ])
            )
        );


        $model->delete() !== false ?
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been removed')) :
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object has not been removed'));

        return $this->redirect($returnUrl);
    }


    public function actionDeleteRule($id, $returnUrl)
    {
        /** @var ExtendedPrice $model */
        $model = ExtendedPriceRule::loadModel(
            $id,
            true,
            false,
            86400,
            new NotFoundHttpException(
                Yii::t('app', "{model} with id :'{id}' not found!", [
                    'model' => Yii::t('app', 'Extended Price Rule'),
                    'id' => $id
                ])
            )
        );


        $model->delete() !== false ?
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been removed')) :
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object has not been removed'));

        return $this->redirect($returnUrl);

    }
}
