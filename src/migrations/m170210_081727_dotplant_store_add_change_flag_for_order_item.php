<?php

use DotPlant\Store\models\order\OrderItem;
use yii\db\Migration;

class m170210_081727_dotplant_store_add_change_flag_for_order_item extends Migration
{
    public function up()
    {
        $this->addColumn(OrderItem::tableName(), 'change_by_manager', $this->double());
    }

    public function down()
    {
        $this->dropColumn(OrderItem::tableName(), 'change_by_manager');
    }
}
