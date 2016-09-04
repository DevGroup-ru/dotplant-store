<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_cart}}".
 *
 * @property integer $id
 * @property integer $is_locked
 * @property integer $is_retail
 * @property string $currency_iso_code
 * @property double $items_count
 * @property string $total_price_with_discount
 * @property string $total_price_without_discount
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_cart}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_locked', 'is_retail', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['currency_iso_code'], 'required'],
            [['items_count', 'total_price_with_discount', 'total_price_without_discount'], 'number'],
            [['currency_iso_code'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'is_locked' => Yii::t('dotplant.store', 'Is Locked'),
            'is_retail' => Yii::t('dotplant.store', 'Is Retail'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency Iso Code'),
            'items_count' => Yii::t('dotplant.store', 'Items Count'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total Price With Discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total Price Without Discount'),
            'created_by' => Yii::t('dotplant.store', 'Created By'),
            'created_at' => Yii::t('dotplant.store', 'Created At'),
            'updated_at' => Yii::t('dotplant.store', 'Updated At'),
        ];
    }
}
