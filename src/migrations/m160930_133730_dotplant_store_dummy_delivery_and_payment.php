<?php

use DotPlant\Store\handlers\SelfPickedDelivery;
use DotPlant\Store\handlers\CashPayment;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\DeliveryTranslation;
use DotPlant\Store\models\order\Payment;
use DotPlant\Store\models\order\PaymentTranslation;
use yii\db\Migration;

class m160930_133730_dotplant_store_dummy_delivery_and_payment extends Migration
{
    public function up()
    {
        foreach(call_user_func([Yii::$app->multilingual->modelsMap['Context'], 'find'])->all() as $context) {
            $this->insert(
                Delivery::tableName(),
                [
                    'context_id' => $context->id,
                    'handler_class_name' => SelfPickedDelivery::class,
                    'packed_json_handler_params' => '{}',
                ]
            );
            $deliveryId = $this->db->lastInsertID;
            $this->insert(
                Payment::tableName(),
                [
                    'context_id' => $context->id,
                    'handler_class_name' => CashPayment::class,
                    'packed_json_handler_params' => '{}',
                ]
            );
            $paymentId = $this->db->lastInsertID;
            foreach ($context->languages as $language) {
                $this->insert(
                    DeliveryTranslation::tableName(),
                    [
                        'model_id' => $deliveryId,
                        'language_id' => $language->id,
                        'name' => 'Self-picked',
                    ]
                );
                $this->insert(
                    PaymentTranslation::tableName(),
                    [
                        'model_id' => $paymentId,
                        'language_id' => $language->id,
                        'name' => 'Cash',
                    ]
                );
            }
        }
    }

    public function down()
    {
        $this->delete(Delivery::tableName(), ['handler_class_name' => SelfPickedDelivery::class]);
        $this->delete(Payment::tableName(), ['handler_class_name' => CashPayment::class]);
    }
}
