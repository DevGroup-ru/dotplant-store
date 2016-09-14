<?php

use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderStatus;
use DotPlant\Store\models\order\Payment;
use yii\db\Migration;

class m160914_114211_dotplant_store_soft_deleting extends Migration
{
    protected $models = [
        Delivery::class,
        Order::class,
        OrderStatus::class,
        Payment::class,
    ];

    public function up()
    {
        foreach ($this->models as $model) {
            $this->addColumn(
                $model::tableName(),
                'is_deleted',
                $this->boolean()->defaultValue(false)
            );
        }
    }

    public function down()
    {
        foreach ($this->models as $model) {
            $this->dropColumn($model::tableName(), 'is_deleted');
        }
    }
}
