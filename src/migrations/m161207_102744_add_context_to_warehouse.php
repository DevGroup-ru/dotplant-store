<?php

use DotPlant\Store\models\warehouse\Warehouse;
use yii\db\Migration;

class m161207_102744_add_context_to_warehouse extends Migration
{
    public function up()
    {
        $this->addColumn(
            Warehouse::tableName(),
            'context_id',
            $this->integer()->null()
        );
    }

    public function down()
    {
        $this->dropColumn(Warehouse::tableName(), 'context_id');
    }
}
