<?php

namespace DotPlant\Store\models\order;

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
 * @property string $total_price_with_discount
 * @property string $total_price_without_discount
 * @property string $seller_price
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
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cart_id', 'order_id', 'goods_id', 'warehouse_id'], 'required'],
            [['cart_id', 'order_id', 'goods_id', 'warehouse_id'], 'integer'],
            [['quantity', 'total_price_with_discount', 'total_price_without_discount', 'seller_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'cart_id' => Yii::t('dotplant.store', 'Cart ID'),
            'order_id' => Yii::t('dotplant.store', 'Order ID'),
            'goods_id' => Yii::t('dotplant.store', 'Goods ID'),
            'warehouse_id' => Yii::t('dotplant.store', 'Warehouse ID'),
            'quantity' => Yii::t('dotplant.store', 'Quantity'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total Price With Discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total Price Without Discount'),
            'seller_price' => Yii::t('dotplant.store', 'Seller Price'),
        ];
    }
}
