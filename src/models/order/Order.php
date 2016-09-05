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
            [['context_id', 'status_id', 'delivery_id', 'payment_id', 'currency_iso_code', 'hash'], 'required'],
            [['context_id', 'status_id', 'delivery_id', 'payment_id', 'is_retail', 'manager_id', 'promocode_id', 'created_by', 'created_at', 'updated_by', 'updated_at', 'forming_time'], 'integer'],
            [['items_count', 'total_price_with_discount', 'total_price_without_discount', 'promocode_discount', 'rate_to_main_currency'], 'number'],
            [['currency_iso_code'], 'string', 'max' => 3],
            [['promocode_name'], 'string', 'max' => 255],
            [['hash'], 'string', 'max' => 32],
            [['hash'], 'unique'],
            [['payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => DotplantStorePayment::className(), 'targetAttribute' => ['payment_id' => 'id']],
            [['delivery_id'], 'exist', 'skipOnError' => true, 'targetClass' => DotplantStoreDelivery::className(), 'targetAttribute' => ['delivery_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => DotplantStoreOrderStatus::className(), 'targetAttribute' => ['status_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'context_id' => Yii::t('dotplant.store', 'Context ID'),
            'status_id' => Yii::t('dotplant.store', 'Status ID'),
            'delivery_id' => Yii::t('dotplant.store', 'Delivery ID'),
            'payment_id' => Yii::t('dotplant.store', 'Payment ID'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency Iso Code'),
            'items_count' => Yii::t('dotplant.store', 'Items Count'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total Price With Discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total Price Without Discount'),
            'is_retail' => Yii::t('dotplant.store', 'Is Retail'),
            'manager_id' => Yii::t('dotplant.store', 'Manager ID'),
            'promocode_id' => Yii::t('dotplant.store', 'Promocode ID'),
            'promocode_discount' => Yii::t('dotplant.store', 'Promocode Discount'),
            'promocode_name' => Yii::t('dotplant.store', 'Promocode Name'),
            'rate_to_main_currency' => Yii::t('dotplant.store', 'Rate To Main Currency'),
            'created_by' => Yii::t('dotplant.store', 'Created By'),
            'created_at' => Yii::t('dotplant.store', 'Created At'),
            'updated_by' => Yii::t('dotplant.store', 'Updated By'),
            'updated_at' => Yii::t('dotplant.store', 'Updated At'),
            'forming_time' => Yii::t('dotplant.store', 'Forming Time'),
            'hash' => Yii::t('dotplant.store', 'Hash'),
        ];
    }
}
