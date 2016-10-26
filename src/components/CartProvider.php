<?php

namespace DotPlant\Store\components;

use DevGroup\Frontend\Universal\ActionData;
use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\DeliveryTranslation;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderItem;
use Yii;
use yii\db\ActiveQuery;

class CartProvider extends DataEntityProvider
{

    /**
     * @var string the region key
     */
    public $regionKey = 'cartRegion';

    /**
     * @var string the material key
     */
    public $materialKey = 'cartMaterial';


    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }

    /**
     * @param ActionData $actionData
     *
     * @return mixed
     */
    public function getEntities(&$actionData)
    {
        $model = Store::getCart();
        $items = $this->getObjectItems($model);
        return [
            $this->regionKey => [
                $this->materialKey => [
                    "items" => $items,
                    "total-price" => CurrencyHelper::format(
                        $model->total_price_with_discount,
                        CurrencyHelper::findCurrencyByIso($model->currency_iso_code)
                    ),
                ],
            ],
        ];
    }

    private function getObjectItems($model)
    {
        $currency = CurrencyHelper::findCurrencyByIso($model->currency_iso_code);
        $isOrder = $model instanceof Order;
        $condition = [$isOrder ? 'order_id' : 'cart_id' => $model->id];
        $items = OrderItem::find()->asArray(true)->where($condition)->all();
        $delivery = null;
        $goods = (new ActiveQuery(Goods::class))
            ->select(['name', 'id'])
            ->where(['id' => array_column($items, 'goods_id')])
            ->innerJoin(
                GoodsTranslation::tableName(),
                'model_id = id AND language_id = :languageId',
                ['languageId' => Yii::$app->multilingual->language_id]
            )
            ->groupBy(['model_id'])
            ->indexBy('id')
            ->column();
        foreach ($items as $index => $item) {
            if ($item['goods_id'] == 0) {
                $delivery = $item;
                if ($isOrder) {
                    $deliveryName = (new ActiveQuery(Delivery::class))
                        ->select(['name'])
                        ->innerJoin(
                            DeliveryTranslation::tableName(),
                            'model_id = id AND language_id = :languageId',
                            ['languageId' => Yii::$app->multilingual->language_id]
                        )
                        ->where(['id' => $model->delivery_id])
                        ->scalar();
                    $delivery['name'] = $deliveryName !== false
                        ? $deliveryName
                        : Yii::t('dotplant.store', 'Unknown delivery');
                } else {
                    $delivery['name'] = Yii::t('dotplant.store', 'Unknown delivery');
                }
                unset($items[$index]);
                continue;
            }
            $items[$index]['name'] = isset($goods[$item['goods_id']])
                ? $goods[$item['goods_id']]
                : Yii::t('dotplant.store', 'Unknown goods');
            $items[$index]['total_price_with_discount'] = CurrencyHelper::format(
                $item['total_price_with_discount'],
                $currency
            );
            $items[$index]['price_with_discount'] = CurrencyHelper::format(
                $item['total_price_with_discount'] / $item['quantity'],
                $currency
            );
        }
        return $items;
    }
}
