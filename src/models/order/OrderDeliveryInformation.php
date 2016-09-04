<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_delivery_information}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $user_id
 * @property integer $country_id
 * @property string $full_name
 * @property string $zip_code
 * @property string $address
 * @property integer $is_allowed
 */
class OrderDeliveryInformation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_delivery_information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'full_name'], 'required'],
            [['order_id', 'user_id', 'country_id', 'is_allowed'], 'integer'],
            [['address'], 'string'],
            [['full_name'], 'string', 'max' => 255],
            [['zip_code'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'order_id' => Yii::t('dotplant.store', 'Order ID'),
            'user_id' => Yii::t('dotplant.store', 'User ID'),
            'country_id' => Yii::t('dotplant.store', 'Country ID'),
            'full_name' => Yii::t('dotplant.store', 'Full Name'),
            'zip_code' => Yii::t('dotplant.store', 'Zip Code'),
            'address' => Yii::t('dotplant.store', 'Address'),
            'is_allowed' => Yii::t('dotplant.store', 'Is Allowed'),
        ];
    }
}
