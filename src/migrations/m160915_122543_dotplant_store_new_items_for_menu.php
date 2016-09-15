<?php

use app\models\BackendMenu;
use yii\db\Migration;

class m160915_122543_dotplant_store_new_items_for_menu extends Migration
{
    protected $items = [
        [
            'name' => 'Orders',
            'icon' => 'fa fa-shopping-cart',
            'added_by_ext' => 'store',
            'rbac_check' => 'backend-view',
            'route' => '/store/orders-manage/index',
            'translation_category' => 'dotplant.store',
            'sort_order' => 10,
        ],
        [
            'name' => 'Payment',
            'icon' => 'fa fa-money',
            'added_by_ext' => 'store',
            'rbac_check' => 'backend-view',
            'route' => '/store/payments-manage/index',
            'translation_category' => 'dotplant.store',
            'sort_order' => 20,
        ],
        [
            'name' => 'Delivery',
            'icon' => 'fa fa-truck',
            'added_by_ext' => 'store',
            'rbac_check' => 'backend-view',
            'route' => '/store/deliveries-manage/index',
            'translation_category' => 'dotplant.store',
            'sort_order' => 30,
        ],
        [
            'name' => 'Order status',
            'icon' => 'fa fa-info-circle',
            'added_by_ext' => 'store',
            'rbac_check' => 'backend-view',
            'route' => '/store/order-statuses-manage/index',
            'translation_category' => 'dotplant.store',
            'sort_order' => 40,
        ],
    ];

    protected function getStoreId()
    {
        $item = BackendMenu::findOne(['parent_id' => 0, 'name' => 'Store']);
        if ($item === null) {
            throw new \Exception('Store item not found');
        }
        return $item->id;
    }

    public function up()
    {
        $id = $this->getStoreId();
        foreach ($this->items as $item) {
            $item['parent_id'] = $id;
            $this->insert(BackendMenu::tableName(), $item);
        }
    }

    public function down()
    {
        $id = $this->getStoreId();
        $this->delete(
            BackendMenu::tableName(),
            ['parent_id' => $id, 'route' => \yii\helpers\ArrayHelper::getColumn($this->items, 'route')]
        );
    }
}
