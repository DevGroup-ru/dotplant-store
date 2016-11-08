<?php

use yii\db\Migration;

class m161108_093450_add_column_original_quantity_in_order_items extends Migration
{
    public function up()
    {
        $this->addColumn(
            \DotPlant\Store\models\order\OrderItem::tableName(),
            "original_quantity",
            "integer"
        );
    }

    public function down()
    {
        echo "m161103_104301_add_column_original_quantity_in_order_items cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}