<?php

use DotPlant\Store\models\order\Cart;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\DeliveryTranslation;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\order\OrderItem;
use DotPlant\Store\models\order\OrderStatus;
use DotPlant\Store\models\order\OrderStatusTranslation;
use DotPlant\Store\models\order\OrderTransaction;
use DotPlant\Store\models\order\Payment;
use DotPlant\Store\models\order\PaymentTranslation;
use yii\db\Migration;

class m160904_143215_dotplant_store_order_init extends Migration
{
    protected function unsignedPrimaryKey($length = null)
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder(\yii\db\mysql\Schema::TYPE_UPK, $length);
    }

    public function up()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        /**
         * Delivery
         */
        $this->createTable(
            Delivery::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'handler_class_name' => $this->string(255),
                'packed_json_handler_params' => $this->text(),
                'sort_order' => $this->integer()->defaultValue(1),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'is_deleted' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );
        $this->createTable(
            DeliveryTranslation::tableName(),
            [
                'model_id' => $this->integer()->unsigned()->notNull(),
                'language_id' => $this->integer()->unsigned()->notNull(),
                'name' => $this->string(255)->notNull(),
                'description' => $this->text(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-dotplant_store_delivery_translation-model_id-language_id',
            DeliveryTranslation::tableName(),
            ['model_id', 'language_id']
        );
        $this->addForeignKey(
            'fk-dotplant_store_delivery_translation-model_id-delivery-id',
            DeliveryTranslation::tableName(),
            'model_id',
            Delivery::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        /**
         * Payment
         */
        $this->createTable(
            Payment::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'handler_class_name' => $this->string(255),
                'packed_json_handler_params' => $this->text(),
                'sort_order' => $this->integer()->defaultValue(1),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'is_deleted' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );
        $this->createTable(
            PaymentTranslation::tableName(),
            [
                'model_id' => $this->integer()->unsigned()->notNull(),
                'language_id' => $this->integer()->unsigned()->notNull(),
                'name' => $this->string(255)->notNull(),
                'description' => $this->text(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-dotplant_store_payment_translation-model_id-language_id',
            PaymentTranslation::tableName(),
            ['model_id', 'language_id']
        );
        $this->addForeignKey(
            'fk-dotplant_store_payment_translation-model_id-payment-id',
            PaymentTranslation::tableName(),
            'model_id',
            Payment::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        /**
         * Order status
         */
        $this->createTable(
            OrderStatus::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'label_class' => $this->string(255),
                'sort_order' => $this->integer()->defaultValue(1),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
                'is_deleted' => $this->boolean()->defaultValue(false),
            ],
            $tableOptions
        );
        $this->createTable(
            OrderStatusTranslation::tableName(),
            [
                'model_id' => $this->integer()->unsigned()->notNull(),
                'language_id' => $this->integer()->unsigned()->notNull(),
                'name' => $this->string(255),
                'label' => $this->string(255)->notNull(),
                'description' => $this->text(),
            ],
            $tableOptions
        );
        $this->addPrimaryKey(
            'pk-dotplant_store_order_status_translation-model_id-language_id',
            OrderStatusTranslation::tableName(),
            ['model_id', 'language_id']
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_status_translation-order_status',
            OrderStatusTranslation::tableName(),
            'model_id',
            OrderStatus::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        /**
         * Cart
         */
        $this->createTable(
            Cart::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'is_locked' => $this->boolean()->notNull()->defaultValue(false),
                'is_retail' => $this->boolean()->notNull()->defaultValue(true),
                'currency_iso_code' => $this->char(3)->notNull(),
                'items_count' => $this->double(),
                'total_price_with_discount' => $this->decimal(10, 2),
                'total_price_without_discount' => $this->decimal(10, 2),
                'created_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
                'user_id' => $this->integer(),
                'packed_json_params' => $this->text(),
            ],
            $tableOptions
        );
        /**
         * Order
         */
        $this->createTable(
            Order::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'status_id' => $this->integer()->unsigned()->notNull(),
                'delivery_id' => $this->integer()->unsigned(),
                'payment_id' => $this->integer()->unsigned(),
                'currency_iso_code' => $this->char(3)->notNull(),
                'items_count' => $this->double(),
                'total_price_with_discount' => $this->decimal(10, 2),
                'total_price_without_discount' => $this->decimal(10, 2),
                'is_retail' => $this->boolean()->notNull()->defaultValue(true),
                'manager_id' => $this->integer()->notNull()->defaultValue(0),
                'promocode_id' => $this->integer()->notNull()->defaultValue(0),
                'promocode_discount' => $this->decimal(10, 2)->notNull()->defaultValue(0),
                'promocode_name' => $this->string(255),
                'rate_to_main_currency' => $this->double()->notNull()->defaultValue(1),
                'created_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_by' => $this->integer(),
                'updated_at' => $this->integer(),
                'forming_time' => $this->integer(),
                'hash' => $this->char(32)->notNull(),
                'is_deleted' => $this->boolean()->defaultValue(false),
                'user_id' => $this->integer(),
            ],
            $tableOptions
        );
        $this->createIndex(
            'uq-dotplant_store_order-hash',
            Order::tableName(),
            'hash',
            true
        );
        $this->addForeignKey(
            'fk-dotplant_store_order-status_id-order_status-id',
            Order::tableName(),
            'status_id',
            OrderStatus::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order-delivery_id-delivery-id',
            Order::tableName(),
            'delivery_id',
            Delivery::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order-payment_id-payment-id',
            Order::tableName(),
            'payment_id',
            Payment::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        // @todo: fk to manager
        // @todo: fk to promocode
        /**
         * Order item
         */
        $this->createTable(
            OrderItem::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'cart_id' => $this->integer()->unsigned(),
                'order_id' => $this->integer()->unsigned(),
                'goods_id' => $this->integer(), // @todo: make it as unsigned column
                'warehouse_id' => $this->integer(), // @todo: make it as unsigned column
                'quantity' => $this->double()->notNull()->defaultValue(0),
                'total_price_with_discount' => $this->decimal(10, 2),
                'total_price_without_discount' => $this->decimal(10, 2),
                'seller_price' => $this->decimal(10, 2),
                'packed_json_params' => $this->string(),
            ],
            $tableOptions
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_item-cart_id-cart-id',
            OrderItem::tableName(),
            'cart_id',
            Cart::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_item-order_id-order-id',
            OrderItem::tableName(),
            'order_id',
            Order::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_item-goods_id-goods-id',
            OrderItem::tableName(),
            'goods_id',
            \DotPlant\Store\models\goods\Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_item-warehouse_id-warehouse-id',
            OrderItem::tableName(),
            'warehouse_id',
            \DotPlant\Store\models\warehouse\Warehouse::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        /**
         * Order transaction
         */
        $this->createTable(
            OrderTransaction::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'order_id' => $this->integer()->unsigned()->notNull(),
                'payment_id' => $this->integer()->unsigned()->notNull(),
                'start_time' => $this->integer()->notNull(),
                'end_time' => $this->integer()->notNull(),
                'sum' => $this->decimal(10, 2)->notNull(),
                'currency_iso_code' => $this->char(3)->notNull(),
                'packed_json_data' => $this->text(),
                'packed_json_result' => $this->text(),
            ],
            $tableOptions
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_transaction-order_id-order-id',
            OrderTransaction::tableName(),
            'order_id',
            Order::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_transaction-order_id-payment-id',
            OrderTransaction::tableName(),
            'payment_id',
            Payment::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        /**
         * Order delivery information
         */
        $this->createTable(
            OrderDeliveryInformation::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'context_id' => $this->integer()->unsigned()->notNull(),
                'order_id' => $this->integer()->unsigned(),
                'user_id' => $this->integer(),
                'country_id' => $this->integer(),
                'full_name' => $this->string(255)->notNull(),
                'zip_code' => $this->string(50),
                'address' => $this->text(),
                'is_allowed' => $this->boolean()->notNull()->defaultValue(true),
            ],
            $tableOptions
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_delivery_information-order_id-order-id',
            OrderDeliveryInformation::tableName(),
            'order_id',
            Order::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_order_delivery_information-user_id-user-id',
            OrderDeliveryInformation::tableName(),
            'user_id',
            \DevGroup\Users\models\User::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable(OrderDeliveryInformation::tableName());
        $this->dropTable(OrderTransaction::tableName());
        $this->dropTable(OrderItem::tableName());
        $this->dropTable(Order::tableName());
        $this->dropTable(Cart::tableName());
        $this->dropTable(OrderStatusTranslation::tableName());
        $this->dropTable(OrderStatus::tableName());
        $this->dropTable(PaymentTranslation::tableName());
        $this->dropTable(Payment::tableName());
        $this->dropTable(DeliveryTranslation::tableName());
        $this->dropTable(Delivery::tableName());
    }
}
