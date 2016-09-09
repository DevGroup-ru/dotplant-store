<?php


namespace DotPlant\Store\models\payment;


use DotPlant\Store\events\PaymentEvent;
use Yii;
use yii\db\ActiveRecord;

/**
 * Class PaymentTransaction
 * @package DotPlant\Store\models\payment
 *
 * This is the model class for table "{{%dotplant_payment_transaction}}".
 *
 * @property integer $id
 * @property string $status
 * @property string $data
 */
class PaymentTransaction extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_payment_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'data'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'status' => Yii::t('dotplant.store', 'Status'),
            'data' => Yii::t('dotplant.store', 'Data'),
        ];
    }

    /**
     * @param PaymentEvent $event
     */
    public function logDataFromEvent($event)
    {
        $this->status = $event->status;
        $this->data = $event->logData;
    }
}