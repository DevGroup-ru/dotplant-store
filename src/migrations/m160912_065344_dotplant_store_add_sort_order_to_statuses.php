<?php

use DotPlant\Store\models\order\OrderStatus;
use yii\db\Migration;

class m160912_065344_dotplant_store_add_sort_order_to_statuses extends Migration
{
    public function up()
    {
        $this->addColumn(
            OrderStatus::tableName(),
            'sort_order',
            $this->integer()->defaultValue(1)->after('label_class')
        );
    }

    public function down()
    {
        $this->dropColumn(OrderStatus::tableName(), 'sort_order');
    }
}
