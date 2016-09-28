<?php

namespace DotPlant\Store\widgets\common;

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\DeliveryTranslation;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\db\ActiveQuery;

class OrderItems extends Widget
{
    private $_isOrder = false;
    public $model;
    public $viewFile = 'backend-order-items';
    public $languageId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->model instanceof Order) {
            $this->_isOrder = true;
        } elseif ($this->model instanceof Cart === false) {
            throw new InvalidParamException(\Yii::t('dotplant.store', 'Bad model class'));
        }
        if ($this->languageId === null) {
            $context = call_user_func(
                [\Yii::$app->multilingual->modelsMap['Context'], 'findOne'],
                $this->model->context_id
            );
            if ($context === null) {
                throw new InvalidParamException(\Yii::t('dotplant.store', 'Wrong context'));
            }
            foreach ($context->languages as $language) {
                if ($language->yii_language == \Yii::$app->language) {
                    $this->languageId = $language->id;
                }
            }
            if ($this->languageId === null) {
                $languages = $context->languages;
                $this->languageId = reset($languages)->id;
            }
        }
        // @todo: add language to query
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $condition = [$this->model instanceof Order? 'order_id' : 'cart_id' => $this->model->id];
        $items = OrderItem::find()->asArray(true)->where($condition)->all();
        $delivery = null;
        $goods = (new ActiveQuery(Goods::class))
            ->select(['name', 'id'])
            ->where(['id' => array_column($items, 'goods_id')])
            ->innerJoin(
                GoodsTranslation::tableName(),
                'model_id = id AND language_id = :languageId',
                ['languageId' => $this->languageId]
            )
            ->groupBy(['model_id'])
            ->indexBy('id')
            ->column();
        foreach ($items as $index => $item) {
            if ($item['goods_id'] == 0) {
                $delivery = $item;
                if ($this->_isOrder) {
                    $deliveryName = (new ActiveQuery(Delivery::class))
                        ->select(['name'])
                        ->innerJoin(
                            DeliveryTranslation::tableName(),
                            'model_id = id AND language_id = :languageId',
                            ['languageId' => $this->languageId]
                        )
                        ->where(['id' => $this->model->delivery_id])
                        ->scalar();
                    $delivery['name'] = $deliveryName !== false
                        ? $deliveryName
                        : \Yii::t('dotplant.store', 'Unknown delivery');
                } else {
                    $delivery['name'] = \Yii::t('dotplant.store', 'Unknown delivery');
                }
                unset($items[$index]);
                continue;
            }
            $items[$index]['name'] = isset($goods[$item['goods_id']])
                ? $goods[$item['goods_id']]
                : \Yii::t('dotplant.store', 'Unknown goods');
        }
        echo $this->render(
            $this->viewFile,
            [
                'delivery' => $delivery,
                'items' => $items,
                'languageId' => $this->languageId,
                'model' => $this->model,
            ]
        );
    }
}
