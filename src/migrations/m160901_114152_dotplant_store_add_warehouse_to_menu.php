<?php

use app\models\BackendMenu;
use DevGroup\TagDependencyHelper\NamingHelper;
use yii\caching\TagDependency;
use yii\db\Migration;

class m160901_114152_dotplant_store_add_warehouse_to_menu extends Migration
{
    public function up()
    {
        $item = BackendMenu::findOne(['parent_id' => 0, 'name' => 'Store']);
        if ($item !== null) {
            $this->insert(
                BackendMenu::tableName(),
                [
                    'parent_id' => $item->id,
                    'name' => 'Warehouses',
                    'icon' => 'fa fa-cubes',
                    'sort_order' => 0,
                    'rbac_check' => 'backend-view',
                    'css_class' => '',
                    'route' => '',
                    'translation_category' => 'dotplant.store',
                    'added_by_ext' => 'store',
                ]
            );
        }
        TagDependency::invalidate(Yii::$app->cache, NamingHelper::getCommonTag(BackendMenu::class));
    }

    public function down()
    {
        $this->delete(BackendMenu::tableName(), ['and', ['!=', 'parent_id', 0], ['name' => 'Warehouses']]);
        TagDependency::invalidate(Yii::$app->cache, NamingHelper::getCommonTag(BackendMenu::class));
    }
}
