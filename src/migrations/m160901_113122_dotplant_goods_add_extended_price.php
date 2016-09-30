<?php

use app\models\BackendMenu;
use DotPlant\Store\handlers\extendedPrice\ProductRule;
use DotPlant\Store\handlers\extendedPrice\StructureRule;
use DotPlant\Store\handlers\extendedPrice\UserGroupRule;
use DotPlant\Store\handlers\extendedPrice\UserRule;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceHandler;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\db\Migration;

class m160901_113122_dotplant_goods_add_extended_price extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        // ExtendedPrice
        $this->createTable(
            ExtendedPrice::tableName(),
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(255)->notNull(),
                'mode' => "enum('-', '%', '=') NOT NULL DEFAULT '%'",
                'is_final' => $this->boolean()->notNull()->defaultValue(0),
                'priority' => $this->integer()->notNull()->defaultValue(0),
                'value' => $this->decimal(10, 2)->notNull()->defaultValue(0),
                'currency_iso_code' => $this->char(3),
                'min_product_price' => $this->decimal(10, 2),
                'start_time' => $this->dateTime(),
                'end_time' => $this->dateTime(),
                'context_id' => $this->integer(),
                'calculator_type' => "enum('goods', 'order') NOT NULL DEFAULT 'goods'",
                'target_class' => "enum('goods', 'order', 'cartItems', 'delivery') NOT NULL DEFAULT 'goods'"
            ],
            $tableOptions
        );
        $this->createIndex(
            'idx-ext-price-calc_t-context-time',
            ExtendedPrice::tableName(),
            ['calculator_type', 'context_id', 'start_time', 'end_time']
        );
        // ExtendedPriceRule
        $this->createTable(
            ExtendedPriceRule::tableName(),
            [
                'id' => $this->primaryKey(),
                'extended_price_id' => $this->integer()->notNull(),
                'extended_price_handler_id' => $this->integer()->notNull(),
                'operand' => "enum('AND', 'OR') NOT NULL DEFAULT 'AND'",
                'priority' => $this->integer(5)->notNull()->defaultValue(0),
                'packed_json_params' => $this->text(),
            ],
            $tableOptions
        );
        $this->createIndex('idx-ext_price_rule-priority', ExtendedPriceRule::tableName(), ['priority']);
        $this->addForeignKey(
            'fk-ep_rules-ep',
            ExtendedPriceRule::tableName(),
            'extended_price_id',
            ExtendedPrice::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        // ExtendedPriceHandler
        $this->createTable(
            ExtendedPriceHandler::tableName(),
            [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'handler_class' => $this->string()->notNull(),
                'target_class' => "enum('all','goods', 'order', 'cartItems', 'delivery') NOT NULL DEFAULT 'all'"
            ],
            $tableOptions
        );
        $this->addForeignKey(
            'fk-ep_rules-ep_handlers',
            ExtendedPriceRule::tableName(),
            'extended_price_handler_id',
            ExtendedPriceHandler::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Data
        $this->batchInsert(
            ExtendedPriceHandler::tableName(),
            [
                'name',
                'handler_class',
                'target_class'
            ],
            [
                [
                    'User',
                    UserRule::class,
                    'all'
                ],
                [
                    'Product',
                    ProductRule::class,
                    'goods'
                ],
                [
                    'User group',
                    UserGroupRule::class,
                    'all'
                ],
                [
                    'Structure rule',
                    StructureRule::class,
                    'goods'
                ]
            ]
        );
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => BackendMenu::find()->select('id')->where(['name'=>'Store'])->scalar(),
                'name' => 'Extended prices',
                'icon' => 'fa fa-ils',
                'sort_order' => 70,
                'rbac_check' => 'dotplant-store-extended-price-view',
                'css_class' => '',
                'route' => '/store/extended-price-manage/index',
                'translation_category' => 'dotplant.store',
                'added_by_ext' => 'store',
            ]
        );
    }

    public function down()
    {
        $this->delete(
            BackendMenu::tableName(),
            ['name' => ['Extended prices']]
        );
        $this->dropTable('{{%dotplant_store_extended_price_rule}}');
        $this->dropTable('{{%dotplant_store_extended_price_handlers}}');
        $this->dropTable('{{%dotplant_store_extended_price}}');
    }
}
