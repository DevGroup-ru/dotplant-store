<?php

namespace DotPlant\Store\models\warehouse;

use DotPlant\Store\exceptions\WarehouseException;
use DotPlant\Store\interfaces\WarehousePriceInterface;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\price\Price;
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
 * @property Goods $goods
 * @property Warehouse $warehouse
 */
class GoodsWarehouse extends \yii\db\ActiveRecord implements WarehousePriceInterface
{
    /**
     * @var array
     */
    private static $_pricesMap = [
        Price::TYPE_SELLER => 'seller_price',
        Price::TYPE_RETAIL => 'retail_price',
        Price::TYPE_WHOLESALE => 'wholesale_price',
    ];

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
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency iso code'),
            'seller_price' => Yii::t('dotplant.store', 'Seller price'),
            'retail_price' => Yii::t('dotplant.store', 'Retail price'),
            'wholesale_price' => Yii::t('dotplant.store', 'Wholesale price'),
            'available_count' => Yii::t('dotplant.store', 'Available count'),
            'reserved_count' => Yii::t('dotplant.store', 'Reserved count'),
            'is_unlimited' => Yii::t('dotplant.store', 'Is unlimited'),
            'is_allowed' => Yii::t('dotplant.store', 'Is allowed'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getPrice($priceType)
    {
        if (!isset(self::$_pricesMap[$priceType])) {
            throw new WarehouseException(Yii::t('dotplant.store', 'Unknown price type'));
        }
        return [
            'isoCode' => $this->currency_iso_code,
            'value' => $this->{self::$_pricesMap[$priceType]}
        ];
    }

    /**
     * @inheritdoc
     */
    public function lockForUpdate()
    {
        $sql = self::find()
                ->where(['goods_id' => $this->goods_id, 'warehouse_id' => $this->warehouse_id])
                ->createCommand()
                ->getRawSql() . ' FOR UPDATE';
        $this->setAttributes(self::findBySql($sql)->asArray(true)->one());
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        return $this->available_count - $this->reserved_count;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGoods()
    {
        return $this->hasOne(Goods::class, ['id' => 'goods_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWarehouse()
    {
        return $this->hasOne(Warehouse::class, ['id' => 'warehouse_id']);
    }
}
