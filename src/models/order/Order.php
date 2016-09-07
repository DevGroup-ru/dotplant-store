<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property integer $status_id
 * @property integer $delivery_id
 * @property integer $payment_id
 * @property string $currency_iso_code
 * @property double $items_count
 * @property string $total_price_with_discount
 * @property string $total_price_without_discount
 * @property integer $is_retail
 * @property integer $manager_id
 * @property integer $promocode_id
 * @property string $promocode_discount
 * @property string $promocode_name
 * @property double $rate_to_main_currency
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 * @property integer $forming_time
 * @property string $hash
 */
class Order extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['context_id', 'status_id', 'currency_iso_code'], 'required'],
            [['context_id', 'status_id', 'delivery_id', 'payment_id', 'is_retail', 'manager_id', 'promocode_id', 'created_by', 'created_at', 'updated_by', 'updated_at', 'forming_time'], 'integer'],
            [['items_count', 'total_price_with_discount', 'total_price_without_discount', 'promocode_discount', 'rate_to_main_currency'], 'number'],
            [['currency_iso_code'], 'string', 'max' => 3],
            [['promocode_name'], 'string', 'max' => 255],
            [['hash'], 'string', 'max' => 32],
            [['hash'], 'unique'],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payment::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => Delivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderStatus::className(), 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'context_id' => Yii::t('dotplant.store', 'Context'),
            'status_id' => Yii::t('dotplant.store', 'Status'),
            'delivery_id' => Yii::t('dotplant.store', 'Delivery'),
            'payment_id' => Yii::t('dotplant.store', 'Payment'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency iso code'),
            'items_count' => Yii::t('dotplant.store', 'Items count'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total price with discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total price without discount'),
            'is_retail' => Yii::t('dotplant.store', 'Is retail'),
            'manager_id' => Yii::t('dotplant.store', 'Manager'),
            'promocode_id' => Yii::t('dotplant.store', 'Promocode'),
            'promocode_discount' => Yii::t('dotplant.store', 'Promocode discount'),
            'promocode_name' => Yii::t('dotplant.store', 'Promocode name'),
            'rate_to_main_currency' => Yii::t('dotplant.store', 'Rate to main currency'),
            'created_by' => Yii::t('dotplant.store', 'Created by'),
            'created_at' => Yii::t('dotplant.store', 'Created at'),
            'updated_by' => Yii::t('dotplant.store', 'Updated by'),
            'updated_at' => Yii::t('dotplant.store', 'Updated at'),
            'forming_time' => Yii::t('dotplant.store', 'Forming time'),
            'hash' => Yii::t('dotplant.store', 'Hash'),
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->hash = md5(uniqid() . time());
        }
        return parent::beforeSave($insert);
    }
}
