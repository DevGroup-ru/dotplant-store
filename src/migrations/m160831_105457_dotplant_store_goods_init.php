<?php

use yii\db\Migration;
use DotPlant\EntityStructure\models\BaseStructure;

class m160831_105457_dotplant_store_goods_init extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable(
            '{{%dotplant_store_goods}}',
            [
                'id' => $this->primaryKey(),
                'vendor_id' => $this->integer()->notNull()->defaultValue(0),
                'parent_id' => $this->integer(),
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
        $this->createIndex('idx-goods-vendor_id', '{{%dotplant_store_goods}}', 'vendor_id');
        $this->createIndex('idx-goods-parent_id', '{{%dotplant_store_goods}}', 'parent_id');
        $this->createIndex('idx-goods-main_structure_id', '{{%dotplant_store_goods}}', 'main_structure_id');
        $this->createIndex('idx-goods-type', '{{%dotplant_store_goods}}', 'type');
        $this->createIndex('idx-goods-sku', '{{%dotplant_store_goods}}', 'sku');
        $this->createIndex('idx-goods-inner_sku', '{{%dotplant_store_goods}}', 'inner_sku');

        $this->createTable(
            '{{%dotplant_store_goods_analog}}',
            [
                'goods_id' => $this->integer()->notNull(),
                'goods_analog_id' => $this->integer()->notNull()
            ],
            $tableOptions
        );

        $this->createIndex('idx-goods_analog-id_analog_id', '{{%dotplant_store_goods_analog}}', ['goods_id', 'goods_analog_id'], true);

        $this->addForeignKey('fk-g-a-goods-goods', '{{%dotplant_store_goods_analog}}', 'goods_id', '{{%dotplant_store_goods}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-g-a-goods-analog', '{{%dotplant_store_goods_analog}}', 'goods_analog_id', '{{%dotplant_store_goods}}', 'id', 'CASCADE');

        $this->createTable(
            '{{%dotplant_store_goods_category}}',
            [
                'structure_id' => $this->integer()->notNull(),
                'goods_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0),
            ],
            $tableOptions
        );

        $this->createIndex('idx-g-c-struct_id-goods_id', '{{%dotplant_store_goods_category}}', ['structure_id', 'goods_id'], true);

        $this->addForeignKey('fk-g-c-goods', '{{%dotplant_store_goods_category}}', 'goods_id', '{{%dotplant_store_goods}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-g-c-structure', '{{%dotplant_store_goods_category}}', 'goods_id', BaseStructure::tableName(), 'id', 'CASCADE');

        $this->createTable(
            '{{%dotplant_store_goods_translation}}',
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
                'announce' => 'LONGTEXT',
                'content' => 'LONGTEXT',
            ],
            $tableOptions
        );

        $this->createIndex('idx-g-t-model_id-lang_id', '{{%dotplant_store_goods_translation}}', ['model_id', 'language_id'], true);
        $this->addForeignKey('fk-g-t-goods', '{{%dotplant_store_goods_translation}}', 'model_id', '{{%dotplant_store_goods}}', 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk-g-a-goods-goods', '{{%dotplant_store_goods_analog}}');
        $this->dropForeignKey('fk-g-a-goods-analog', '{{%dotplant_store_goods_analog}}');
        $this->dropForeignKey('fk-g-c-goods', '{{%dotplant_store_goods_category}}');
        $this->dropForeignKey('fk-g-c-structure', '{{%dotplant_store_goods_category}}');
        $this->dropForeignKey('fk-g-t-goods', '{{%dotplant_store_goods_translation}}');

        $this->dropIndex('idx-goods-vendor_id', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods-parent_id', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods-main_structure_id', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods-type', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods-sku', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods-inner_sku', '{{%dotplant_store_goods}}');
        $this->dropIndex('idx-goods_analog-id_analog_id', '{{%dotplant_store_goods_analog}}');
        $this->dropIndex('idx-g-c-struct_id-goods_id', '{{%dotplant_store_goods_category}}');
        $this->dropIndex('idx-g-t-model_id-lang_id', '{{%dotplant_store_goods_translation}}');

        $this->dropTable('{{%dotplant_store_goods}}');
        $this->dropTable('{{%dotplant_store_goods_analog}}');
        $this->dropTable('{{%dotplant_store_goods_category}}');
        $this->dropTable('{{%dotplant_store_goods_translation}}');
    }
}
