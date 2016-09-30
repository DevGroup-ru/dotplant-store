<?php

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use DotPlant\Store\models\warehouse\Warehouse;
use DotPlant\Store\models\warehouse\WarehouseTranslation;
use yii\db\Migration;

class m160901_072910_dotplant_store_warehouse_init extends Migration
{
    public function up()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        // Warehouse
        $this->createTable(
            Warehouse::tableName(),
            [
                'id' => $this->primaryKey(),
                'type' => $this->smallInteger()->notNull()->defaultValue(Warehouse::TYPE_WAREHOUSE),
                'priority' => $this->smallInteger()->defaultValue(1),
            ],
            $tableOptions
        );

        // WarehouseTranslation
        $this->createTable(
            WarehouseTranslation::tableName(),
            [
                'model_id' => $this->integer()->notNull(),
                'language_id' => $this->integer()->notNull(),
                'name' => $this->string(255)->notNull(),
                'address' => $this->text(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-warehouse_translation-model_id-language_id',
            WarehouseTranslation::tableName(),
            ['model_id', 'language_id']
        );
        $this->addForeignKey(
            'fk-warehouse_translation-model_id-warehouse-id',
            WarehouseTranslation::tableName(),
            'model_id',
            Warehouse::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        // GoodsWarehouse
        $this->createTable(
            GoodsWarehouse::tableName(),
            [
                'goods_id' => $this->integer()->notNull(),
                'warehouse_id' => $this->integer()->notNull(),
                'currency_iso_code' => $this->string(3)->notNull(),
                'seller_price' => $this->decimal(10, 2),
                'retail_price' => $this->decimal(10, 2),
                'wholesale_price' => $this->decimal(10, 2),
                'available_count' => $this->double()->notNull()->defaultValue(0),
                'reserved_count' => $this->double()->notNull()->defaultValue(0),
                'is_unlimited' => $this->boolean()->defaultValue(false),
                'is_allowed' => $this->boolean()->defaultValue(true),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-goods_warehouse-goods_id-warehouse_id',
            GoodsWarehouse::tableName(),
            ['goods_id', 'warehouse_id']
        );
        $this->addForeignKey(
            'fk-goods_warehouse-warehouse_id-warehouse-id',
            GoodsWarehouse::tableName(),
            'warehouse_id',
            Warehouse::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-goods_warehouse-goods_id-goods-id',
            GoodsWarehouse::tableName(),
            'goods_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable(GoodsWarehouse::tableName());
        $this->dropTable(WarehouseTranslation::tableName());
        $this->dropTable(Warehouse::tableName());
    }
}
