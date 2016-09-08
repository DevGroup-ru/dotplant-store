<?php

namespace DotPlant\Store\models\order;

use DevGroup\Users\models\User;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_delivery_information}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property integer $order_id
 * @property integer $user_id
 * @property integer $country_id
 * @property string $full_name
 * @property string $email
 * @property string $phone
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
            [['context_id', 'full_name', 'email'], 'required'],
            [['context_id', 'order_id', 'user_id', 'country_id', 'is_allowed'], 'integer'],
            [['address'], 'string'],
            [['full_name'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 100],
            [['phone'], 'string', 'max' => 20],
            [['zip_code'], 'string', 'max' => 50],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id'],
            ],
            [
                ['order_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Order::className(),
                'targetAttribute' => ['order_id' => 'id'],
            ],
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
            'order_id' => Yii::t('dotplant.store', 'Order'),
            'user_id' => Yii::t('dotplant.store', 'User'),
            'country_id' => Yii::t('dotplant.store', 'Country'),
            'full_name' => Yii::t('dotplant.store', 'Full name'),
            'email' => Yii::t('dotplant.store', 'E-mail'),
            'phone' => Yii::t('dotplant.store', 'Phone number'),
            'zip_code' => Yii::t('dotplant.store', 'Zip code'),
            'address' => Yii::t('dotplant.store', 'Address'),
            'is_allowed' => Yii::t('dotplant.store', 'Is allowed'),
        ];
    }
}
