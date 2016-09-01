<?php

namespace DotPlant\Store\models\warehouse;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_goods_warehouse}}".
 *
 * @property integer $goods_id
 * @property integer $warehouse_id
 * @property string $currency_iso_code
 * @property string $seller_price
 * @property string $retail_price
 * @property string $wholesale_price
 * @property double $available_count
 * @property double $reserved_count
 * @property integer $is_unlimited
 * @property integer $is_allowed
 */
class GoodsWarehouse extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_goods_warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'warehouse_id', 'currency_iso_code'], 'required'],
            [['goods_id', 'warehouse_id', 'is_unlimited', 'is_allowed'], 'integer'],
            [['seller_price', 'retail_price', 'wholesale_price', 'available_count', 'reserved_count'], 'number'],
            [['currency_iso_code'], 'string', 'max' => 3],
            [
                ['goods_id'],
                'exist',
                'targetClass' => Goods::class,
                'targetAttribute' => ['goods_id' => 'id'],
            ],
            [
                ['warehouse_id'],
                'exist',
                'targetClass' => Warehouse::class,
                'targetAttribute' => ['warehouse_id' => 'id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => Yii::t('dotplant.store', 'Goods ID'),
            'warehouse_id' => Yii::t('dotplant.store', 'Warehouse ID'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency Iso Code'),
            'seller_price' => Yii::t('dotplant.store', 'Seller Price'),
            'retail_price' => Yii::t('dotplant.store', 'Retail Price'),
            'wholesale_price' => Yii::t('dotplant.store', 'Wholesale Price'),
            'available_count' => Yii::t('dotplant.store', 'Available Count'),
            'reserved_count' => Yii::t('dotplant.store', 'Reserved Count'),
            'is_unlimited' => Yii::t('dotplant.store', 'Is Unlimited'),
            'is_allowed' => Yii::t('dotplant.store', 'Is Allowed'),
        ];
    }
}
