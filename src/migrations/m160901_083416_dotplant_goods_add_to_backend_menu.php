<?php

use yii\db\Migration;
use app\models\BackendMenu;
use yii\caching\TagDependency;
use DevGroup\TagDependencyHelper\NamingHelper;

class m160901_083416_dotplant_goods_add_to_backend_menu extends Migration
{
    public function up()
    {
        $this->insert(
            BackendMenu::tableName(),
            [
                'parent_id' => 0,
                'name' => 'Store',
                'icon' => 'fa fa-shopping-basket',
                'sort_order' => 0,
                'rbac_check' => 'backend-view',
                'css_class' => 'header',
                'route' => '',
                'translation_category' => 'dotplant.store',
                'added_by_ext' => 'store',
            ]
        );

        $storeItemId = $this->db->getLastInsertID();

        $this->batchInsert(
            BackendMenu::tableName(),
            [
                'parent_id',
                'name',
                'icon',
                'sort_order',
                'rbac_check',
                'css_class',
                'route',
                'translation_category',
                'added_by_ext'
            ],
            [
                [
                    $storeItemId,
                    'Categories',
                    'fa fa-folder',
                    0,
                    'backend-view',
                    '',
                    '/store/goods-category-manage',
                    'dotplant.store',
                    'store'
                ],
                [
                    $storeItemId,
                    'Products',
                    'fa fa-list',
                    0,
                    'backend-view',
                    '',
                    '/store/goods-manage',
                    'dotplant.store',
                    'store'
                ]
            ]
        );
        TagDependency::invalidate(Yii::$app->cache, NamingHelper::getCommonTag(BackendMenu::class));
    }

    public function down()
    {
        $this->delete(
            BackendMenu::tableName(),
            ['name' => ['Store', 'Categories', 'Products']]
        );
        TagDependency::invalidate(Yii::$app->cache, NamingHelper::getCommonTag(BackendMenu::class));
    }
}
