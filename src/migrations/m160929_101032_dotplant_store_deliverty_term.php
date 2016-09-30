<?php

use DotPlant\Store\handlers\warehouseDelivery\WarehouseFixedDeliveryHandler;
use yii\db\Migration;

class m160929_101032_dotplant_store_deliverty_term extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%dotplant_store_warehouse%}}',
            'handler_class',
            $this->string()->notNull()->defaultValue(addslashes(WarehouseFixedDeliveryHandler::class))
        );

        $this->addColumn(
            '{{%dotplant_store_warehouse%}}',
            'packed_json_params',
            $this->text()->notNull()
        );

        $this->update(
            '{{%dotplant_store_warehouse%}}',
            [
                'packed_json_params' => '{}'
            ]
        );
    }

    public function down()
    {
        $this->dropColumn(
            '{{%dotplant_store_warehouse%}}',
            'handler_class'
        );
        $this->dropColumn(
            '{{%dotplant_store_warehouse%}}',
            'packed_json_params'
        );
    }

}
