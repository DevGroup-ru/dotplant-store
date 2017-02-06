<?php

use DevGroup\DataStructure\helpers\PropertiesTableGenerator;
use DotPlant\Store\models\order\Order;
use yii\db\Migration;

class m170206_100223_order_props extends Migration
{
    public function up()
    {
        PropertiesTableGenerator::getInstance()->generate(Order::class);
    }

    public function down()
    {
        PropertiesTableGenerator::getInstance()->drop(Order::class);
    }

}
