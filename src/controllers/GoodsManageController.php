<?php

namespace DotPlant\Store\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DotPlant\Store\models\goods\Goods;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class GoodsManageController
 *
 * @package app\vendor\dotplant\store\src\controllers
 */
class GoodsManageController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new Goods(['is_active' => '']);
//        if (null !== $id) {
//            $searchModel->parent_id = (int)$id;
//        }
//        if (null !== $context_id) {
//            $searchModel->context_id = (int)$context_id;
//        }
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

    public function actionEdit($id = null, $type = null, $parent_id = null)
    {
        if (null !== $id) {
            if (null === $goods = Goods::get($id)) {
                throw new NotFoundHttpException(
                    Yii::t('dotplant.store', '{model} with #{id} not found!', [
                        'model' => Yii::t('dotplant.store', 'Goods'),
                        'id' => $id
                    ])
                );
            }
        } else {
            $goods = Goods::create($type);
        }
        $canSave = true; //Yii::$app->user->can('');
        $refresh = !$goods->isNewRecord;
        if (false === $goods->isNewRecord) {
            $goods->translations;
        } else {
            $goods->loadDefaultValues();
            if (null !== $parent_id) {
                $goods->parent_id = $parent_id;
            }
        }
        $post = Yii::$app->request->post();
        //\yii\helpers\VarDumper::dump($post,10,1); die();
        if (false === empty($post)) {
            if (false === $canSave) {
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
            if (true === $goods->load($post)) {
                foreach (Yii::$app->request->post('GoodsTranslation', []) as $language => $data) {
                    foreach ($data as $attribute => $translation) {
                        $goods->translate($language)->$attribute = $translation;
                    }
                }
                if (true === $goods->validate()) {
                    if (true === $goods->save(false)) {
                        Yii::$app->session->setFlash(
                            'success',
                            Yii::t('dotplant.store', '{model} successfully saved!')
                        );
                        if (true === $refresh) {
                            return $this->refresh();
                        } else {
                            return $this->redirect(['/store/goods-manage/edit', 'id' => $goods->id]);
                        }
                    } else {
                        Yii::$app->session->setFlash(
                            'error',
                            Yii::t('dotplant.store', 'An error occurred while saving {model}!')
                        );
                    }
                } else {
                    Yii::$app->session->setFlash('warning', Yii::t(
                        'dotplant.store',
                        'Please verify that all fields are filled correctly!'
                    ));
                }
            }
        }
        return $this->render(
            'edit',
            [
                'goods' => $goods,
                'canSave' => true,
            ]
        );
    }
}
