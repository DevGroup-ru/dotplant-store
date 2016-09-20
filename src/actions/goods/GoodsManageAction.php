<?php

namespace DotPlant\Store\actions\goods;

use DevGroup\AdminUtils\actions\BaseAdminAction;
use DevGroup\DataStructure\behaviors\HasProperties;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\Store\models\goods\Goods;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Class GoodsManageAction
 *
 * @package DotPlant\Store\actions
 */
class GoodsManageAction extends BaseAdminAction
{
    /**
     * @param null $product_id
     * @param null $id
     * @param null $type
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \DotPlant\Store\exceptions\GoodsException
     */
    public function run($product_id = null, $id = null, $type = null)
    {
        /** @var Goods | MultilingualActiveRecord | MultilingualTrait | HasProperties $goods */
        if (null !== $product_id) {
            if (null === $goods = Goods::get($product_id)) {
                throw new NotFoundHttpException(
                    Yii::t(
                        'dotplant.store',
                        '{model} with #{id} not found!',
                        [
                            'model' => Yii::t('dotplant.store', 'Goods'),
                            'id' => $product_id,
                        ]
                    )
                );
            }
        } else {
            $type = (null === $type) ? Goods::TYPE_PRODUCT : $type;
            $goods = Goods::create($type);
        }
        $canSave = true; //Yii::$app->user->can('');
        $refresh = !$goods->isNewRecord;
        if (false === $goods->isNewRecord) {
            $goods->translations;
        } else {
            $goods->loadDefaultValues();
        }
        $post = Yii::$app->request->post();
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
                        $goodsFormName = $goods->formName();
                        $categories = isset($post[$goodsFormName]['categories']) ? $post[$goodsFormName]['categories'] : [];
                        $categories = array_unique($categories);
                        CategoryGoods::saveBindings($goods->id, $categories);
                        Yii::$app->session->setFlash('success',
                            Yii::t(
                                'dotplant.store',
                                '{model} successfully saved!',
                                ['model' => Yii::t('dotplant.store', $goodsFormName)]
                            )
                        );
                        if (true === $refresh) {
                            return $this->controller->refresh();
                        } else {
                            return $this->controller->redirect(
                                ['/structure/entity-manage/goods-manage', 'product_id' => $goods->id]
                            );
                        }
                    } else {
                        Yii::$app->session->setFlash(
                            'error',
                            Yii::t('dotplant.store', 'An error occurred while saving {model}!')
                        );
                    }
                } else {
                    Yii::$app->session->setFlash(
                        'warning',
                        Yii::t(
                            'dotplant.store',
                            'Please verify that all fields are filled correctly!'
                        )
                    );
                }
            }
        }
        return $this->controller->render(
            '@DotPlant/Store/views/goods-manage/edit',
            [
                'goods' => $goods,
                'canSave' => true,
                'undefinedType' => $goods->isNewRecord,
                'startCategory' => $id,
            ]
        );
    }
}
