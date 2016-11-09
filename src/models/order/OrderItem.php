<?php

namespace DotPlant\Store\models\order;

use DevGroup\DataStructure\behaviors\PackedJsonAttributes;
use DotPlant\Store\components\calculator\OrderItemCalculator;
use DotPlant\Store\components\calculator\OrderItemDeliveryCalculator;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\warehouse\Warehouse;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_item}}".
 *
 * @property integer $id
 * @property integer $cart_id
 * @property integer $order_id
 * @property integer $goods_id
 * @property integer $warehouse_id
 * @property double $quantity
 * @property double $total_price_with_discount
 * @property double $total_price_without_discount
 * @property double $seller_price
 * @property string $packed_json_params
 *
 * @property Order $order
 * @property Cart $cart
 * @property Warehouse $warehouse
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_item}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'PackedJsonAttributes' => [
                'class' => PackedJsonAttributes::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cart_id', 'order_id', 'goods_id', 'warehouse_id'], 'integer'],
            [['quantity', 'total_price_with_discount', 'total_price_without_discount', 'seller_price'], 'number'],
            [
                ['warehouse_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Warehouse::className(),
                'targetAttribute' => ['warehouse_id' => 'id']
            ],
            [
                ['cart_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Cart::className(),
                'targetAttribute' => ['cart_id' => 'id']
            ],
            [
                ['goods_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Goods::className(),
                'targetAttribute' => ['goods_id' => 'id'],
                'when' => function ($model) {
                    return $model->goods_id != null;
                },
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::className(),
                'targetAttribute' => ['order_id' => 'id']
            ],
            [['warehouse_id'], 'validateWarehouse'],
            [['packed_json_params'], 'string'],
        ];
    }

    /**
     *
     */
    public function validateWarehouse()
    {
        if (!empty($this->order_id) && empty($this->warehouse_id)) {
            $this->addError('warehouse_id', Yii::t('yii', '{attribute} cannot be blank.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'cart_id' => Yii::t('dotplant.store', 'Cart'),
            'order_id' => Yii::t('dotplant.store', 'Order'),
            'goods_id' => Yii::t('dotplant.store', 'Goods'),
            'warehouse_id' => Yii::t('dotplant.store', 'Warehouse'),
            'quantity' => Yii::t('dotplant.store', 'Quantity'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total price with discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total price without discount'),
            'seller_price' => Yii::t('dotplant.store', 'Seller price'),
        ];
    }

    /**
     * Cart relation
     * @return \yii\db\ActiveQuery
     */
    public function getCart()
    {
        return $this->hasOne(Cart::class, ['id' => 'cart_id']);
    }

    /**
     * Order relation
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::class, ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }

    /**
     * @throws OrderException
     */
    public function calculate()
    {
        if ($this->isDelivery()) {
            $price = OrderItemDeliveryCalculator::getPrice($this);
        } else {
            if (Warehouse::hasEnoughQuantity(
                $this->goods_id,
                $this->quantity,
                $this->warehouse_id
            ) === false
            ) {
                throw new OrderException(Yii::t('dotplant.store', 'The warehouse has no enough goods'));
            }
            // @todo: Add a check warehouse count

            $price = OrderItemCalculator::getPrice($this);
        }

        $this->total_price_without_discount = $price['totalPriceWithoutDiscount'];
        $this->total_price_with_discount = $price['totalPriceWithDiscount'];
        $this->quantity = $price['items'];
        $this->params = ArrayHelper::merge($this->params, ['extendedPrice' => $price['extendedPrice']]);
    }


    /**
     * @return bool|int
     */
    public function getDeliveryTerm()
    {
        return $this->isDelivery() === true ? false : OrderItemCalculator::getDeliveryTerm($this);
    }

    /**
     * @param $id
     * @return Goods
     * @throws OrderException
     */
    protected function findGoods($id)
    {
        $model = Goods::get($id);
        if ($model === null && $this->isDelivery() === false) {
            throw new OrderException(Yii::t('dotplant.store', 'Goods not found'));
        }
        return $model;
    }

    /**
     * @return bool
     */
    public function isDelivery()
    {
        if (is_null($this->goods_id) === true) {
            return true;
        }
        return false;
    }
}
