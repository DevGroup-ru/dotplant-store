<?php

namespace DotPlant\Store\actions\goods;

use DevGroup\AdminUtils\actions\BaseAdminAction;
use DevGroup\AdminUtils\events\ModelEditAction;
use DevGroup\AdminUtils\traits\BackendRedirect;
use DevGroup\DataStructure\behaviors\HasProperties;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsParent;
use DotPlant\Store\models\goods\GoodsTranslation;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use DotPlant\Store\models\warehouse\Warehouse;
use yii\base\Model;
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

    const EVENT_BEFORE_INSERT = 'dotplant.store.goodsBeforeInsert';
    const EVENT_BEFORE_UPDATE = 'dotplant.store.goodsBeforeUpdate';
    const EVENT_AFTER_INSERT = 'dotplant.store.goodsAfterInsert';
    const EVENT_AFTER_UPDATE = 'dotplant.store.goodsAfterUpdate';

    const EVENT_FORM_BEFORE_SUBMIT = 'dotplant.store.goodsFormBeforeSubmit';
    const EVENT_FORM_AFTER_SUBMIT = 'dotplant.store.goodsFormAfterSubmit';

    const EVENT_BEFORE_FORM = 'dotplant.store.goodsBeforeForm';
    const EVENT_AFTER_FORM = 'dotplant.store.goodsAfterForm';

    use BackendRedirect;

    /**
     * @param null $product_id
     * @param null $id
     * @param null $type
     * @return string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \DotPlant\Store\exceptions\GoodsException
     */
    public function run($product_id = null, $id = null, $type = Goods::TYPE_PRODUCT)
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
            $goods = Goods::create($type);
        }
        $canSave = true; //Yii::$app->user->can('');
        /**@var Goods[] $child */
        $child = [];
        /**@var GoodsWarehouse[] $prices */
        $prices = [];
        $goods->autoSaveProperties = true;
        if (false === $goods->isNewRecord) {
            $goods->translations;
            if ($goods->getHasChild() === false) {
                $prices = GoodsWarehouse::find()
                    ->indexBy('warehouse_id')
                    ->where(['goods_id' => $goods->id])
                    ->all();
            } else {
                $child = $goods->getChildren()
                    ->select([GoodsTranslation::tableName() . '.name', Goods::tableName() . '.id'])
                    ->indexBy('id')
                    ->asArray()
                    ->column();
            }
        } else {
            $goods->loadDefaultValues();
        }
        if ($goods->getHasChild() === false) {
            foreach (Warehouse::find()->asArray()->all() as $warehouse) {
                if (!isset($prices[$warehouse['id']])) {
                    $price = new GoodsWarehouse(['warehouse_id' => $warehouse['id']]);
                    $price->loadDefaultValues();
                    $prices[$warehouse['id']] = $price;
                }
            }
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
                $event = new ModelEditAction($goods);
                $event->isValid = $goods->validate();
                $usedPriceWarehouses = [];
                Model::loadMultiple($prices, $post);
                foreach ($prices as $warehouseIs => $price) {
                    if ($price->seller_price !== '' ||
                        $price->retail_price !== '' &&
                        $price->wholesale_price !== ''
                    ) {
                        $usedPriceWarehouses[] = $warehouseIs;
                    }
                }
                if (empty($usedPriceWarehouses) === true && $goods->getHasChild() === false) {
                    $goods->addError(
                        'id',
                        Yii::t(
                            'dotplant.store',
                            'Please add price!'
                        )
                    );
                    $event->isValid = false;
                }

                $goods->isNewRecord === true ?
                    $this->trigger(self::EVENT_BEFORE_INSERT, $event) :
                    $this->trigger(self::EVENT_BEFORE_UPDATE, $event);

                if (true === $event->isValid) {
                    if (true === $goods->save(false)) {
                        $goods->isNewRecord === true ?
                            $this->trigger(self::EVENT_AFTER_INSERT, $event) :
                            $this->trigger(self::EVENT_AFTER_UPDATE, $event);

                        $goodsFormName = $goods->formName();
                        $categories = isset($post[$goodsFormName]['categories']) ? $post[$goodsFormName]['categories'] : [];
                        $categories = array_unique($categories);
                        CategoryGoods::saveBindings($goods->id, $categories);
                        if ($goods->getHasChild() === true) {
                            $childGoods = isset($post['childGoods']) ? $post['childGoods'] : [];
                            GoodsParent::deleteAll([
                                'goods_parent_id' => $goods->id
                            ]);
                            foreach ($childGoods as $key => $childId) {
                                (new GoodsParent([
                                    'goods_id' => $childId,
                                    'goods_parent_id' => $goods->id,
                                    'sort_order' => $key
                                ]))->save();
                            }
                        } else {
                            /**@var GoodsWarehouse[] $prices */
                            foreach ($prices as $warehouseIs => $price) {
                                $price->goods_id = $goods->id;
                                if (in_array($warehouseIs, $usedPriceWarehouses)) {
                                    $price->save();
                                } elseif ($price->isNewRecord === false) {
                                    $price->delete();
                                }
                            }
                        }
                        $this->redirectUser(
                            $id,
                            true,
                            ['/structure/entity-manage/products'],
                            ['/structure/entity-manage/goods-manage', 'product_id' => $goods->id]
                        );
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
                'child' => $child,
                'prices' => $prices,
                'canSave' => true,
                'undefinedType' => $goods->isNewRecord,
                'startCategory' => $id,
            ]
        );
    }


    public function redirect($url)
    {
        return $this->controller->redirect($url);
    }
}
