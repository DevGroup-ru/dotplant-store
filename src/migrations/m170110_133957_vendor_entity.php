<?php

use DevGroup\DataStructure\helpers\PropertiesTableGenerator;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\models\vendor\Vendor;
use yii\db\Migration;

class m170110_133957_vendor_entity extends Migration
{
    public function up()
    {
        PropertiesTableGenerator::getInstance()->generate(Vendor::class);
    }

    public function down()
    {
        PropertiesTableGenerator::getInstance()->drop(Vendor::class);
    }

}
