<?php

use yii\db\Migration;
use DotPlant\Store\models\goods\Goods;
use DotPlant\EntityStructure\models\BaseStructure;

class m160915_102108_dotplant_store_vendor_category_goods_analog extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable(
            '{{%dotplant_store_category_goods}}',
            [
                'structure_id' => $this->integer()->notNull(),
                'goods_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0)
            ],
            $tableOptions
        );
        $this->createIndex(
            'idx-category_goods-struct_id-goods_id',
            '{{%dotplant_store_category_goods}}',
            ['goods_id', 'structure_id'],
            true
        );
        $this->addForeignKey(
            'fk-category_goods-goods',
            '{{%dotplant_store_category_goods}}',
            'goods_id',
            Goods::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-category_goods-structure',
            '{{%dotplant_store_category_goods}}',
            'structure_id',
            BaseStructure::tableName(),
            'id',
            'CASCADE'
        );

        $this->createTable(
            '{{%dotplant_store_vendor}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->notNull(),
                'created_at' => $this->integer(),
                'created_by' => $this->integer(),
                'updated_at' => $this->integer(),
                'updated_by' => $this->integer(),
                'is_deleted' => $this->integer(),
                'packed_json_data' => $this->text(),
            ],
            $tableOptions
        );
        $this->addForeignKey(
            'fk-vendor-id-goods-vendor_id',
            Goods::tableName(),
            'vendor_id',
            '{{%dotplant_store_vendor}}',
            'id'
        );

        $this->createTable(
            '{{%dotplant_store_vendor_translation}}',
            [
                'model_id' => $this->integer()->notNull(),
                'language_id' => $this->integer()->notNull(),
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

        $this->createIndex('idx-vt-model_id-lang_id', '{{%dotplant_store_vendor_translation}}', ['model_id', 'language_id'], true);
        $this->addForeignKey(
            'fk-vt-vendor',
            '{{%dotplant_store_vendor_translation}}',
            'model_id',
            '{{%dotplant_store_vendor}}',
            'id',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-category_goods-goods', '{{%dotplant_store_category_goods}}');
        $this->dropForeignKey('fk-category_goods-structure', '{{%dotplant_store_category_goods}}');
        $this->dropForeignKey('fk-vendor-id-goods-vendor_id', Goods::tableName());
        $this->dropForeignKey('fk-vt-vendor','{{%dotplant_store_vendor_translation}}');
        $this->dropIndex('idx-category_goods-struct_id-goods_id', '{{%dotplant_store_category_goods}}');
        $this->dropIndex('idx-vt-model_id-lang_id', '{{%dotplant_store_vendor_translation}}');

        $this->dropTable('{{%dotplant_store_vendor}}');
        $this->dropTable('{{%dotplant_store_category_goods}}');
        $this->dropTable('{{%dotplant_store_vendor_translation}}');
    }
}
