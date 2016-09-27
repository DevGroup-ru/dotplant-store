<?php

namespace DotPlant\Store\widgets\common;

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\DeliveryTranslation;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use yii\base\Widget;
use yii\db\ActiveQuery;

class OrderItems extends Widget
{
    public $model;
    public $viewFile = 'backend-order-items';
    public $languageId;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // @todo: check model class name. The widget allows Order an Cart only
        if ($this->languageId === null) {
            $this->languageId = \Yii::$app->multilingual->language_id;
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
            ->innerJoin(GoodsTranslation::tableName(), 'model_id = id')
            ->groupBy(['model_id'])
            ->indexBy('id')
            ->column();
        foreach ($items as $index => $item) {
            if ($item['goods_id'] == 0) {
                $deliveryName = (new ActiveQuery(Delivery::class)) // @todo: Skip if model is Cart
                    ->select(['name'])
                    ->innerJoin(DeliveryTranslation::tableName(), 'model_id = id')
                    ->where(['id' => $this->model->delivery_id])
                    ->scalar();
                $delivery = $item;
                $delivery['name'] = $deliveryName !== false
                    ? $deliveryName
                    : \Yii::t('dotplant.store', 'Unknown delivery');
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
            ]
        );
    }
}
