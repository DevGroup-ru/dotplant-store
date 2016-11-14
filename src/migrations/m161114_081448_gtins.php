<?php

use DotPlant\Store\models\goods\Goods;
use yii\db\Migration;

class m161114_081448_gtins extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $this->addColumn(
            Goods::tableName(),
            'asin',
            $this->string(10)->notNull()->defaultValue('')
        );
        $this->addColumn(
            Goods::tableName(),
            'isbn',
            $this->string(18)->notNull()->defaultValue('')
        );
        $this->addColumn(
            Goods::tableName(),
            'upc',
            $this->string(13)->notNull()->defaultValue('')
        );
        $this->addColumn(
            Goods::tableName(),
            'ean',
            $this->string(13)->notNull()->defaultValue('')
        );
        $this->addColumn(
            Goods::tableName(),
            'jan',
            $this->string(13)->notNull()->defaultValue('')
        );
        $this->createIndex('asinIndex', Goods::tableName(), ['asin']);
    }

    public function down()
    {
        $this->dropColumn(Goods::tableName(), 'asin');
        $this->dropColumn(Goods::tableName(), 'isbn');
        $this->dropColumn(Goods::tableName(), 'upc');
        $this->dropColumn(Goods::tableName(), 'ean');
        $this->dropColumn(Goods::tableName(), 'jan');
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
