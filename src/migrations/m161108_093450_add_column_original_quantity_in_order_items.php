<?php

use DotPlant\Store\models\order\OrderItem;
use yii\db\Migration;

class m161108_093450_add_column_original_quantity_in_order_items extends Migration
{
    public function up()
    {
        $this->addColumn(OrderItem::tableName(), 'original_quantity', $this->double());
    }

    public function down()
    {
        $this->dropColumn(OrderItem::tableName(), 'original_quantity');
    }
}
