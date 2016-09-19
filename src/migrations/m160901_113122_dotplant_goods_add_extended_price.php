<?php


use app\helpers\PermissionsHelper;
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

    public static $permissionsConfig = [
        'ExtendedPriceAdministrator' => [
            'descr' => 'Extended Price Administrator Role',
            'permits' => [
                'store-extended-price-view' => 'View extended price grid',
                'store-extended-price-edit' => 'Edit extended price',
                'store-extended-price-create' => 'Create extended price',
                'store-extended-price-delete' => 'Delete extended price',
            ],
        ],
    ];

    public function up()
    {
        PermissionsHelper::createPermissions(self::$permissionsConfig);

        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => BackendMenu::find()->select('id')->where(['name'=>'Store'])->scalar(),
                'name' => 'Extended prices',
                'icon' => 'fa fa-ils',
                'sort_order' => 70,
                'rbac_check' => 'store-extended-price-view',
                'css_class' => '',
                'route' => '/store/extended-price-manage/index',
                'translation_category' => 'dotplant.store',
                'added_by_ext' => 'store',
            ]
        );

        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable(
            ExtendedPrice::tableName(),
            [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'mode' => "enum('-', '%', '=') NOT NULL DEFAULT '%'",
                'is_final' => $this->boolean()->notNull()->defaultValue(0),
                'priority' => $this->integer(5)->notNull()->defaultValue(0),
                'value' => $this->decimal(10, 2)->notNull()->defaultValue(0),
                'currency_iso_code' => $this->string(),
                'min_product_price' => $this->decimal(10, 2),
                'start_time' => $this->dateTime(),
                'end_time' => $this->dateTime(),
                'context_id' => $this->integer(),
                'calculator_type' => "enum('goods', 'order') NOT NULL DEFAULT 'goods'",
                'target_class' => "enum('goods', 'order', 'cartItems', 'delivery') NOT NULL DEFAULT 'goods'"
            ],
            $tableOptions
        );

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

        $this->createIndex('idx-ext-price-calc_t-context-time', '{{%dotplant_store_extended_price}}',
            ['calculator_type', 'context_id', 'start_time', 'end_time']);
        $this->createIndex('idx-ext_price_rule-priority', '{{%dotplant_store_extended_price_rule}}', ['priority']);

        $this->addForeignKey('fk-ep_rules-ep', '{{%dotplant_store_extended_price_rule}}', 'extended_price_id',
            '{{%dotplant_store_extended_price}}', 'id', 'CASCADE');
        $this->addForeignKey('fk-ep_rules-ep_handlers', '{{%dotplant_store_extended_price_rule}}',
            'extended_price_handler_id', '{{%dotplant_store_extended_price_handlers}}', 'id', 'CASCADE');


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


    }

    public function down()
    {
        PermissionsHelper::removePermissions(self::$permissionsConfig);

        $this->delete(
            BackendMenu::tableName(),
            ['name' => ['Extended prices']]
        );

        $this->dropForeignKey('fk-ep_rules-ep', '{{%dotplant_store_extended_price_rule}}');
        $this->dropForeignKey('fk-ep_rules-ep_handlers', '{{%dotplant_store_extended_price_rule}}');

        $this->dropIndex('idx-ext-price-calc_t-context-time', '{{%dotplant_store_extended_price}}');
        $this->dropIndex('idx-ext_price_rule-priority', '{{%dotplant_store_extended_price_rule}}');

        $this->dropTable('{{%dotplant_store_extended_price}}');
        $this->dropTable('{{%dotplant_store_extended_price_rule}}');
        $this->dropTable('{{%dotplant_store_extended_price_handlers}}');
    }
}
