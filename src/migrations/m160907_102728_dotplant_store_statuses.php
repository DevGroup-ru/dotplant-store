<?php

use DevGroup\Multilingual\models\Context;
use DevGroup\Multilingual\models\Language;
use DotPlant\Store\models\order\OrderStatus;
use yii\db\Migration;

class m160907_102728_dotplant_store_statuses extends Migration
{
    public function up()
    {
        $statuses = [
            [
                'label_class' => 'label label-default',
                'name' => 'Forming',
                'label' => 'Forming',
            ],
            [
                'label_class' => 'label label-warning',
                'name' => 'Waiting for a payment',
                'label' => 'Waiting for a payment',
            ],
            [
                'label_class' => 'label label-danger',
                'name' => 'Paid',
                'label' => 'Paid',
            ],
            [
                'label_class' => 'label label-success',
                'name' => 'Processing',
                'label' => 'Processing',
            ],
            [
                'label_class' => 'label label-info',
                'name' => 'Delivering',
                'label' => 'Delivering',
            ],
            [
                'label_class' => 'label label-primary',
                'name' => 'Delivered',
                'label' => 'Delivered',
            ],
            [
                'label_class' => 'label label-primary',
                'name' => 'Done',
                'label' => 'Done',
            ],
            [
                'label_class' => 'label label-primary',
                'name' => 'Canceled',
                'label' => 'Canceled',
            ],
        ];
        $contexts = Context::find()->all();
        foreach ($statuses as $index => $status) {
            foreach ($contexts as $context) {
                /** @var OrderStatus|\DevGroup\Multilingual\behaviors\MultilingualActiveRecord $model */
                $model = new OrderStatus;
                $model->loadDefaultValues();
                $model->label_class = $status['label_class'];
                $model->context_id = $context->id;
                $model->sort_order = $index + 1;
                foreach ($context->languages as $language) {
                    $model->translate($language->id)->name = $status['name'];
                    $model->translate($language->id)->label = $status['label'];
                }
                $model->save();
            }
        }
    }

    public function down()
    {
        return true;
    }
}
