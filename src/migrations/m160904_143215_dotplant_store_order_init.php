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
                'id' => $this->unsignedPrimaryKey($length = null),
                'handler_class_name' => $this->string(255),
                'packed_json_handler_params' => $this->text(),
                'sort_order' => $this->integer()->defaultValue(1),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
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
            'pk-dotplant_store_delivery_translation',
            DeliveryTranslation::tableName(),
            ['model_id', 'language_id']
        );
        // @todo: add fk to main table
        /**
         * Payment
         */
        $this->createTable(
            Payment::tableName(),
            [
                'id' => $this->unsignedPrimaryKey($length = null),
                'handler_class_name' => $this->string(255),
                'packed_json_handler_params' => $this->text(),
                'sort_order' => $this->integer()->defaultValue(1),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
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
            'pk-dotplant_store_payment_translation',
            PaymentTranslation::tableName(),
            ['model_id', 'language_id']
        );
        /**
         * Order status
         */
        $this->createTable(
            OrderStatus::tableName(),
            [
                'id' => $this->unsignedPrimaryKey($length = null),
                'label_class' => $this->string(255),
                'is_active' => $this->boolean()->notNull()->defaultValue(true),
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
            'pk-dotplant_store_order_status_translation',
            OrderStatusTranslation::tableName(),
            ['model_id', 'language_id']
        );
        // @todo: add fk to main table
        /**
         * Cart
         */
        $this->createTable(
            Cart::tableName(),
            [
                'id' => $this->unsignedPrimaryKey($length = null),
                'is_locked' => $this->boolean()->notNull()->defaultValue(false),
                'is_retail' => $this->boolean()->notNull()->defaultValue(true),
                'currency_iso_code' => $this->char(3)->notNull(),
                'items_count' => $this->double(),
                'total_price_with_discount' => $this->decimal(10, 2),
                'total_price_without_discount' => $this->decimal(10, 2),
                'created_by' => $this->integer(),
                'created_at' => $this->integer(),
                'updated_at' => $this->integer(),
            ],
            $tableOptions
        );
        /**
         * Order
         */
        $this->createTable(
            Order::tableName(),
            [
                'id' => $this->unsignedPrimaryKey($length = null),
                'status_id' => $this->integer()->unsigned()->notNull(),
                'delivery_id' => $this->integer()->unsigned()->notNull(),
                'payment_id' => $this->integer()->unsigned()->notNull(),
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
            ],
            $tableOptions
        );
        $this->createIndex(
            'uq-dotplant_store_order-hash',
            Order::tableName(),
            'hash',
            true
        );
        /**
         * Order item
         */
        $this->createTable(
            OrderItem::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'cart_id' => $this->integer()->unsigned()->notNull(),
                'order_id' => $this->integer()->unsigned()->notNull(),
                'goods_id' => $this->integer()->notNull(), // @todo: make it as unsigned column
                'warehouse_id' => $this->integer()->notNull(), // @todo: make it as unsigned column
                'quantity' => $this->double()->notNull()->defaultValue(0),
                'total_price_with_discount' => $this->decimal(10, 2),
                'total_price_without_discount' => $this->decimal(10, 2),
                'seller_price' => $this->decimal(10, 2),
            ],
            $tableOptions
        );
        // @todo: fk to cart table
        // @todo: fk to order table
        // @todo: fk to goods table
        // @todo: fk to warehouse table
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
        // @todo: fk to order table
        // @todo: fk to payment table
        /**
         * Order delivery information
         */
        $this->createTable(
            OrderDeliveryInformation::tableName(),
            [
                'id' => $this->unsignedPrimaryKey(),
                'order_id' => $this->integer()->unsigned()->notNull(),
                'user_id' => $this->integer()->notNull(),
                'country_id' => $this->integer(),
                'full_name' => $this->string(255)->notNull(),
                'zip_code' => $this->string(50),
                'address' => $this->text(),
                'is_allowed' => $this->boolean()->notNull()->defaultValue(true),
            ],
            $tableOptions
        );
        // @todo: fk to order table
        // @todo: fk to user table
    }

    public function down()
    {
        echo "m160904_143215_dotplant_store_order_init cannot be reverted.\n";
        return false;
    }
}
