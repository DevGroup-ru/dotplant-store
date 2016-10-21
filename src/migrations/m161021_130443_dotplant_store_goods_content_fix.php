<?php

use DotPlant\Store\models\goods\GoodsTranslation;
use yii\db\Migration;

class m161021_130443_dotplant_store_goods_content_fix extends Migration
{
    public function up()
    {
        $this->renameColumn(GoodsTranslation::tableName(), 'content', 'description');
    }

    public function down()
    {
        $this->renameColumn(GoodsTranslation::tableName(), 'description', 'content');
    }
}
