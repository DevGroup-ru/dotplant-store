<?php

namespace DotPlant\Store\controllers;


use DevGroup\AdminUtils\controllers\BaseController;
use devgroup\JsTreeWidget\actions\AdjacencyList\TreeNodesReorderAction;
use DotPlant\EntityStructure\actions\BaseEntityAutocompleteAction;
use DotPlant\EntityStructure\actions\BaseEntityDeleteAction;
use DotPlant\EntityStructure\actions\BaseEntityEditAction;
use DotPlant\EntityStructure\actions\BaseEntityListAction;
use DotPlant\EntityStructure\actions\BaseEntityRestoreAction;
use DotPlant\EntityStructure\actions\BaseEntityTreeAction;
use DotPlant\EntityStructure\actions\BaseEntityTreeMoveAction;
use DotPlant\Store\models\goods\GoodsCategory;

/**
 * Class GoodsCategoryManageController
 *
 * @package DotPlant\Store\controllers
 */
class GoodsCategoryManageController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index' => [
                'class' => BaseEntityListAction::class,
                'entityClass' => GoodsCategory::class,
                //'viewFile' => '@DotPlant/Content/views/pages-manage/index'
            ],
            'edit' => [
                'class' => BaseEntityEditAction::class,
                'entityClass' => GoodsCategory::class,
                //'viewFile' => '@DotPlant/Content/views/pages-manage/edit',
                //'permission' => 'dotplant-content-edit'
            ],
            'autocomplete' => [
                'class' => BaseEntityAutocompleteAction::class,
                'entityClass' => GoodsCategory::class,
            ],
            'delete' => [
                'class' => BaseEntityDeleteAction::class,
                'entityClass' => GoodsCategory::class,
            ],
            'restore' => [
                'class' => BaseEntityRestoreAction::class,
                'entityClass' => GoodsCategory::class,
            ],
            'get-tree' => [
                'class' => BaseEntityTreeAction::class,
                'className' => GoodsCategory::class,
                //todo configure it
                'showHiddenInTree' => true,
            ],
            'tree-reorder' => [
                'class' => TreeNodesReorderAction::class,
                'className' => GoodsCategory::class,
            ],
            'tree-parent' => [
                'class' => BaseEntityTreeMoveAction::class,
                'className' => GoodsCategory::class,
                'saveAttributes' => ['parent_id', 'context_id']
            ],

        ];
    }
    
    public function actionIndex()
    {

    }
}