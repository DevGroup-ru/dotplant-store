<?php

namespace DotPlant\Store\models\order;

use DevGroup\DataStructure\behaviors\PackedJsonAttributes;
use DotPlant\Store\events\PaymentEvent;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_transaction}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $payment_id
 * @property integer $start_time
 * @property integer $end_time
 * @property string $sum
 * @property string $currency_iso_code
 * @property string $packed_json_data
 * @property string $packed_json_result
 */
class OrderTransaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_transaction}}';
    }

    public function behaviors()
    {
        return [
            [
                'class' => PackedJsonAttributes::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'payment_id', 'start_time', 'end_time', 'sum', 'currency_iso_code'], 'required'],
            [['order_id', 'payment_id', 'start_time', 'end_time'], 'integer'],
            [['sum'], 'number'],
            [['packed_json_data', 'packed_json_result'], 'string'],
            [['currency_iso_code'], 'string', 'max' => 3],
            [
                ['payment_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Payment::className(),
                'targetAttribute' => ['payment_id' => 'id'],
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
            'order_id' => Yii::t('dotplant.store', 'Order'),
            'payment_id' => Yii::t('dotplant.store', 'Payment'),
            'start_time' => Yii::t('dotplant.store', 'Start Time'),
            'end_time' => Yii::t('dotplant.store', 'End time'),
            'sum' => Yii::t('dotplant.store', 'Sum'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency iso code'),
            'packed_json_data' => Yii::t('dotplant.store', 'Data'),
            'packed_json_result' => Yii::t('dotplant.store', 'Result'),
        ];
    }

    /**
     * @param PaymentEvent $event
     */
    public function logDataFromEvent($event)
    {
        $this->order_id = $event->order_id;
        $this->payment_id = $event->payment_id;
        $this->start_time = $event->start_time;
        $this->end_time = $event->end_time;
        $this->sum = $event->sum;
        $this->currency_iso_code = $event->currency_iso_code;
        $this->data = $event->payment_data;
        $this->result = $event->payment_result;
    }
}
