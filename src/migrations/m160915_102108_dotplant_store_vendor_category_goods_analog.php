<?php

use yii\db\Migration;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\vendor\Vendor;
use DotPlant\Store\models\vendor\VendorTranslation;

class m160915_102108_dotplant_store_vendor_category_goods_analog extends Migration
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
            CategoryGoods::tableName(),
            [
                'structure_id' => $this->integer()->notNull(),
                'goods_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0)
            ],
            $tableOptions
        );
        $this->createIndex(
            'idx-category_goods-struct_id-goods_id',
            CategoryGoods::tableName(),
            ['goods_id', 'structure_id'],
            true
        );
        $this->addForeignKey(
            'fk-category_goods-goods',
            CategoryGoods::tableName(),
            'goods_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-category_goods-structure',
            CategoryGoods::tableName(),
            'structure_id',
            BaseStructure::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createTable(
            Vendor::tableName(),
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
            Vendor::tableName(),
            'id'
        );
        $this->createTable(
            VendorTranslation::tableName(),
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
                'announce' => $longText,
                'content' => $longText,
            ],
            $tableOptions
        );
        $this->addPrimaryKey('pk-vt-model_id-lang_id', VendorTranslation::tableName(), ['model_id', 'language_id']);
        $this->addForeignKey(
            'fk-vt-vendor',
            VendorTranslation::tableName(),
            'model_id',
            Vendor::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-vendor-id-goods-vendor_id', Goods::tableName());
        $this->dropTable(VendorTranslation::tableName());
        $this->dropTable(Vendor::tableName());
        $this->dropTable(CategoryGoods::tableName());
    }
}
