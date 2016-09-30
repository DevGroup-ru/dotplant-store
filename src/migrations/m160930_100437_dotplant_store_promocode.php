<?php

use app\helpers\PermissionsHelper;
use DotPlant\Store\handlers\extendedPrice\PromocodeRule;
use DotPlant\Store\models\extendedPrice\ExtendedPriceHandler;
use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\Promocode;
use yii\db\Migration;

class m160930_100437_dotplant_store_promocode extends Migration
{
    private static $permissionsConfig = [
        'StoreBackendPromocodeManager' => [
            'descr' => 'Backend Store Promocode Manager Role',
            'permits' => [
                'dotplant-store-promocode-view' => 'Backend Promocode View',
                'dotplant-store-promocode-edit' => 'Backend Promocode Edit',
            ]
        ],
        'StoreBackendPromocodeAdministrator' => [
            'descr' => 'Backend Store Promocode Administrator Role',
            'permits' => [
                'dotplant-store-promocode-create' => 'Backend Promocode Create',
                'dotplant-store-promocode-delete' => 'Backend Promocode Delete',
            ],
            'roles' => [
                'StoreBackendPromocodeManager'
            ],
        ],
    ];

    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable(
            Promocode::tableName(),
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(250)->notNull(),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'promocode_string' => $this->string(250)->notNull()->unique(),
                'is_unlimited' => $this->boolean()->notNull()->defaultValue(false),
                'available_count' => $this->integer()->defaultValue(0),
            ],
            $tableOptions
        );
        $this->createTable(
            '{{%dotplant_store_order_promocode}}',
            [
                'order_id' => $this->integer()->unsigned(),
                'promocode_id' => $this->integer(),
                'promocode_name' => $this->string(250)->notNull(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'order_id-promocode_id',
            '{{%dotplant_store_order_promocode}}',
            ['order_id', 'promocode_id']
        );
        $this->addForeignKey(
            'fk-order_promocode-promocode',
            '{{%dotplant_store_order_promocode}}',
            'promocode_id',
            Promocode::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-order_promocode-order',
            '{{%dotplant_store_order_promocode}}',
            'order_id',
            Order::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->createTable(
            '{{%dotplant_store_cart_promocode}}',
            [
                'cart_id' => $this->integer()->unsigned(),
                'promocode_id' => $this->integer(),
                'promocode_name' => $this->string(250)->notNull(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey('cart_id-promocode_id', '{{%dotplant_store_cart_promocode}}', ['promocode_id', 'cart_id']);
        $this->addForeignKey(
            'fk-cart_promocode-promocode',
            '{{%dotplant_store_cart_promocode}}',
            'promocode_id',
            Promocode::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-cart_promocode-cart',
            '{{%dotplant_store_cart_promocode}}',
            'cart_id',
            Cart::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->dropColumn(Order::tableName(), 'promocode_id');
        $this->dropColumn(Order::tableName(), 'promocode_discount');
        $this->dropColumn(Order::tableName(), 'promocode_name');

        $this->insert(
            ExtendedPriceHandler::tableName(),
            [
                'name' => 'Promocode',
                'handler_class' => PromocodeRule::class,
                'target_class' => 'order',
            ]
        );

        PermissionsHelper::createPermissions(self::$permissionsConfig);
    }

    public function down()
    {
        PermissionsHelper::removePermissions(self::$permissionsConfig);
        $this->delete(ExtendedPriceHandler::tableName(), ['handler_class' => PromocodeRule::class,]);

        $this->addColumn(Order::tableName(), 'promocode_name', $this->string(255));
        $this->addColumn(Order::tableName(), 'promocode_discount', $this->decimal(10, 2)->notNull()->defaultValue(0));
        $this->addColumn(Order::tableName(), 'promocode_id', $this->integer()->notNull()->defaultValue(0));

        $this->dropTable('{{%dotplant_store_cart_promocode}}');
        $this->dropTable('{{%dotplant_store_order_promocode}}');
        $this->dropTable(Promocode::tableName());
    }
}
