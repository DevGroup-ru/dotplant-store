<?php

use DotPlant\Store\models\order\OrderDeliveryInformation;
use yii\db\Migration;

class m160908_075018_dotplant_store_add_fields_to_delivery_information extends Migration
{
    public function up()
    {
        $this->addColumn(
            OrderDeliveryInformation::tableName(),
            'email',
            $this->string(100)->notNull()->after('full_name')
        );
        $this->addColumn(
            OrderDeliveryInformation::tableName(),
            'phone',
            $this->string(20)->after('email')
        );
    }

    public function down()
    {
        $this->dropColumn(OrderDeliveryInformation::tableName(), 'phone');
        $this->dropColumn(OrderDeliveryInformation::tableName(), 'email');
    }
}
