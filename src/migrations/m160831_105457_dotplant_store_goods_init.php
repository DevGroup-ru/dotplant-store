<?php

use yii\db\Migration;
use DevGroup\DataStructure\helpers\PropertiesTableGenerator;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;

class m160831_105457_dotplant_store_goods_init extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $longText = $this->db->driverName === 'mysql'
            ? 'LONGTEXT'
            : $this->text();
        $this->createTable(
            Goods::tableName(),
            [
                'id' => $this->primaryKey(),
                'vendor_id' => $this->integer(),
                'main_structure_id' => $this->integer(),
                'type' => $this->integer()->notNull()->defaultValue(1),
                'role' => $this->integer(),
                'sku' => $this->string(255)->notNull(),
                'inner_sku' => $this->string(255),
                'is_deleted' => $this->boolean()->notNull()->defaultValue(false),
                'created_at' => $this->integer(),
                'created_by' => $this->integer(),
                'updated_at' => $this->integer(),
                'updated_by' => $this->integer(),
            ],
            $tableOptions
        );
        $this->createIndex('idx-goods-vendor_id', Goods::tableName(), 'vendor_id');
        $this->createIndex('idx-dotplant_store_goods-main_structure_id', Goods::tableName(), 'main_structure_id'); // @todo: fk?
        $this->createIndex('idx-dotplant_store_goods-type', Goods::tableName(), 'type');
        $this->createIndex('idx-dotplant_store_goods-sku', Goods::tableName(), 'sku');
        $this->createIndex('idx-dotplant_store_goods-inner_sku', Goods::tableName(), 'inner_sku');

        // Translations
        $this->createTable(
            GoodsTranslation::tableName(),
            [
                'model_id' => $this->integer()->notNull(),
                'language_id' => $this->integer()->notNull(),
                'name' => $this->string(255)->notNull(),
                'title' => $this->string(255),
                'h1' => $this->string(255),
                'breadcrumbs_label' => $this->string(255),
                'meta_description' => $this->string(400),
                'slug' => $this->string(80)->notNull(),
                'url' => $this->string(800),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'announce' => $longText,
                'content' => $longText,
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-dotplant_store_goods_translation-model_id-language_id',
            GoodsTranslation::tableName(),
            ['model_id', 'language_id']
        );
//        $this->createIndex(
//            'uq-ds-gt-language_id-url',
//            GoodsTranslation::tableName(),
//            ['url', 'language_id'],
//            true
//        );
        $this->addForeignKey(
            'fk-dotplant_store_goods_translation-model_id-goods-id',
            '{{%dotplant_store_goods_translation}}',
            'model_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Analog
        // @todo: implement via Model::tableName()
        $this->createTable(
            '{{%dotplant_store_goods_analog}}',
            [
                'goods_id' => $this->integer()->notNull(),
                'goods_analog_id' => $this->integer()->notNull()
            ],
            $tableOptions
        );
        $this->createIndex(
            'idx-goods_analog-id_analog_id',
            '{{%dotplant_store_goods_analog}}',
            ['goods_id', 'goods_analog_id'],
            true
        );
        $this->addForeignKey(
            'fk-g-a-goods-goods',
            '{{%dotplant_store_goods_analog}}',
            'goods_id', Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-g-a-goods-analog',
            '{{%dotplant_store_goods_analog}}',
            'goods_analog_id', Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        PropertiesTableGenerator::getInstance()->generate(Goods::class);
    }

    public function down()
    {
        PropertiesTableGenerator::getInstance()->drop(Goods::class);
        $this->dropTable(GoodsTranslation::tableName());
        $this->dropTable('{{%dotplant_store_goods_analog}}');
        $this->dropTable(Goods::tableName());
    }
}
