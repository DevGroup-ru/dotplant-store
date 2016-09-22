<?php

use app\helpers\PermissionsHelper;
use yii\db\Migration;

class m160921_104220_dotplant_store_permissions extends Migration
{
    private static $rules = [
        'OrderStatusAdministrator' => [
            'descr' => 'You can administrate order statuses',
            'permits' => [
                'dotplant-store-order-status-view' => 'You can see order statuses',
                'dotplant-store-order-status-create' => 'You can create a new order status',
                'dotplant-store-order-status-edit' => 'You can edit an order status',
                'dotplant-store-order-status-delete' => 'You can delete an order status',
            ],
        ],
        'DeliveryAdministrator' => [
            'descr' => 'You can administrate deliveries',
            'permits' => [
                'dotplant-store-delivery-view' => 'You can see deliveries',
                'dotplant-store-delivery-create' => 'You can create a new delivery',
                'dotplant-store-delivery-edit' => 'You can edit a delivery',
                'dotplant-store-delivery-delete' => 'You can delete a delivery',
            ],
        ],
        'PaymentAdministrator' => [
            'descr' => 'You can administrate payments',
            'permits' => [
                'dotplant-store-payment-view' => 'You can see payments',
                'dotplant-store-payment-create' => 'You can create a new payment',
                'dotplant-store-payment-edit' => 'You can edit a payment',
                'dotplant-store-payment-delete' => 'You can delete a payment',
            ],
        ],
        'WarehouseAdministrator' => [
            'descr' => 'You can administrate warehouses',
            'permits' => [
                'dotplant-store-warehouse-view' => 'You can see warehouses',
                'dotplant-store-warehouse-create' => 'You can create a new warehouse',
                'dotplant-store-warehouse-edit' => 'You can edit a warehouse',
                'dotplant-store-warehouse-delete' => 'You can delete a warehouse',
            ],
        ],
        'VendorAdministrator' => [
            'descr' => 'You can administrate vendors',
            'permits' => [
                'dotplant-store-vendor-view' => 'You can see warehouses',
                'dotplant-store-vendor-create' => 'You can create a new warehouse',
                'dotplant-store-vendor-edit' => 'You can edit a warehouse',
                'dotplant-store-vendor-delete' => 'You can delete a warehouse',
            ],
        ],
        'GoodsAdministrator' => [
            'descr' => 'You can administrate goods',
            'permits' => [
                'dotplant-store-goods-view' => 'You can see goods',
                'dotplant-store-goods-create' => 'You can create a new goods',
                'dotplant-store-goods-edit' => 'You can edit a goods',
                'dotplant-store-goods-delete' => 'You can delete a goods',
            ],
        ],
        'ExtendedPriceAdministrator' => [
            'descr' => 'You can administrate extended prices',
            'permits' => [
                'dotplant-store-extended-price-view' => 'You can see extended prices',
                'dotplant-store-extended-price-create' => 'You can create a new extended price',
                'dotplant-store-extended-price-edit' => 'You can edit a extended price',
                'dotplant-store-extended-price-delete' => 'You can delete a extended price',
            ],
        ],
        'OrderManager' => [
            'descr' => 'You can manage orders',
            'permits' => [
                'dotplant-store-order-view' => 'You can see orders',
                'dotplant-store-order-create' => 'You can create a new order',
                'dotplant-store-order-edit' => 'You can edit a order',
            ]
        ],
        'OrderAdministrator' => [
            'descr' => 'You can administrate order',
            'permits' => [
                'dotplant-store-order-delete' => 'You can delete a order',
            ],
            'roles' => [
                'OrderManager',
            ],
        ],
        'StoreAdministrator' => [
            'descr' => 'You can administrate the store module',
            'roles' => [
                'OrderStatusAdministrator',
                'DeliveryAdministrator',
                'PaymentAdministrator',
                'WarehouseAdministrator',
                'VendorAdministrator',
                'GoodsAdministrator',
                'ExtendedPriceAdministrator',
                'OrderAdministrator',
            ],
        ],
    ];

    public function up()
    {
        PermissionsHelper::createPermissions(static::$rules);
    }

    public function down()
    {
        PermissionsHelper::removePermissions(static::$rules);
    }
}
