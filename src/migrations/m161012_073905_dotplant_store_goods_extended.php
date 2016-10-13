<?php

use DotPlant\Store\models\goods\GoodsExtended;
use DotPlant\Store\models\goods\GoodsTranslation;
use yii\db\Migration;

class m161012_073905_dotplant_store_goods_extended extends Migration
{
    public function up()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $longText = $this->db->driverName === 'mysql' ? 'LONGTEXT NOT NULL' : $this->text()->notNull();
        $this->createTable(
            GoodsExtended::tableName(),
            [
                'model_id' => $this->integer(),
                'language_id' => $this->integer(),
                'packed_json_content' => $longText,
                'packed_json_providers' => $longText,
                'template_id' => $this->integer()->notNull()->defaultValue(0),
                'layout_id' => $this->integer()->notNull()->defaultValue(0),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-dotplant_store_goods_ext-model_id-language_id',
            GoodsExtended::tableName(),
            ['model_id', 'language_id']
        );
        $this->addForeignKey(
            'fk-dotplant_store_goods_ext-goods_translation',
            GoodsExtended::tableName(),
            ['model_id', 'language_id'],
            GoodsTranslation::tableName(),
            ['model_id', 'language_id'],
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable(GoodsExtended::tableName());
    }
}
