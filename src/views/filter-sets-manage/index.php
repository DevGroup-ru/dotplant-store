<?php
/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var  \DotPlant\EntityStructure\models\BaseStructureSearch $searchModel
 * @var int $parentId
 */

use devgroup\JsTreeWidget\widgets\TreeWidget;
use \devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use DotPlant\EntityStructure\models\BaseStructure;

?>
<div class="row">
    <div class="col-sm-12 col-md-4">
        <?php
        $contextMenu = BaseStructure::getContextMenu(
            [
                'open' => [
                    'label' => 'Open',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/store/filter-sets-manage/index'],
                        ['parent_id', 'context_id', 'id']
                    ),
                ],
                'edit' => [
                    'label' => 'Edit',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/structure/entity-manage/edit']
                    ),
                ],
            ]
        );
        ?>
        <?= TreeWidget::widget(
            [
                'treeDataRoute' => [
                    '/structure/entity-manage/get-tree',
                    'selected_id' => $parentId,
                    'contextId' => 'all',
                ],
                'reorderAction' => ['/structure/entity-manage/tree-reorder'],
                'changeParentAction' => ['/structure/entity-manage/tree-parent'],
                'treeType' => TreeWidget::TREE_TYPE_ADJACENCY,
                'contextMenuItems' => $contextMenu,
            ]
        ) ?>
    </div>
    <div class="col-sm-12 col-md-8">
        <div class="entities__list-entities box box-solid">
            <div class="box-header with-border clearfix">
                <h3 class="box-title pull-left">

                </h3>
            </div>

        </div>
    </div>
</div>
