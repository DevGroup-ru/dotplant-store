<?php
/**
 * @var $this View
 * @var array $selectorData
 * @var StructureFilterSets[] $filterSets
 * @var int $parentId
 */

use DevGroup\AdminUtils\columns\ActionColumn;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use \devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\filters\StructureFilterSets;
use kartik\editable\Editable;
use kartik\grid\EditableColumn;
use kartik\grid\GridView;
use yii\bootstrap\Dropdown;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\Pjax;

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
                            ['id']
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
                        <?= Yii::t('app', 'Filter Sets') ?>
                    </h3>
                </div>
                <div class="box-body">
                    <?php Pjax::begin() ?>
                    <?php if (empty($selectorData) === false): ?>
                        <div class="dropdown">
                            <?= Html::button(
                                'Add set <span class="caret"></span></button>',
                                ['type' => 'button', 'class' => 'btn btn-default', 'data-toggle' => 'dropdown']
                            ); ?>
                            <?= Dropdown::widget(['items' => $selectorData]); ?>
                        </div>
                    <?php else: ?>
                        <p><?= Yii::t('app', 'Select entity first') ?></p>
                    <?php endif; ?>
                    <?php if (empty($filterSets) === false): ?>
                        <?= GridView::widget(
                            [
                                'dataProvider' => (new ArrayDataProvider(['allModels' => $filterSets,])),
                                'id' => 'filters-grid',
                                'columns' => [
                                    [
                                        'value' => function (StructureFilterSets $model) {
                                            return $model->getPropertyName();
                                        },
                                        'attribute' => 'propertyName',
                                    ],
                                    [
                                        'value' => function (StructureFilterSets $model) {
                                            return $model->getPropertyGroupName();
                                        },
                                        'attribute' => 'propertyGroupName',
                                    ],
                                    [
                                        'class' => EditableColumn::className(),
                                        'attribute' => 'delegate_to_child',
                                        'value' => function (StructureFilterSets $model) {
                                            return var_export($model->isDelegatableToChildren(), true);
                                        },
                                        'editableOptions' => [
                                            'inputType' => Editable::INPUT_SWITCH,
                                            'formOptions' => [
                                                'action' => 'update-set',
                                            ],
                                            'name' => 'delegate_to_child',
                                        ],
                                    ],
                                    [
                                        'class' => ActionColumn::class,
                                        'appendReturnUrl' => false,
                                        'buttons' => function ($model, $key, $index, $column) {

                                            $result = [
                                                'toggle' => [
                                                    'url' => '#',
                                                    'icon' => 'caret-down',
                                                    'class' => 'btn-info toggle-filter-values',
                                                    'label' => Yii::t('dotplant.store', 'Show'),
                                                    'options' => ['data' => ['for' => $index, 'pjax' => 0]],
                                                ],
                                                'delete' => [
                                                    'url' => 'delete-set',
                                                    'icon' => 'trash-o',
                                                    'class' => 'btn-danger',
                                                    'label' => Yii::t('dotplant.store', 'Delete'),
                                                    'keyParam' => 'indx',
                                                    'options' => [
                                                        'data-action' => 'delete',
                                                    ],
                                                ],
                                            ];
                                            return $result;
                                        },
                                    ],
                                ],
                                'afterRow' => function (StructureFilterSets $model, $key, $index, GridView $grid) {
                                    $gridForFilterValues = GridView::widget(
                                        [
                                            'dataProvider' => (new ArrayDataProvider(
                                                ['allModels' => $model->getFilterValuesAsArray(), 'pagination' => false]
                                            )),
                                            'layout' => "{items}",
                                            'columns' => [
                                                [
                                                    'class' => EditableColumn::className(),
                                                    'attribute' => 'value',
                                                    'editableOptions' => [
                                                        'formOptions' => [
                                                            'action' => 'update-set-value',
                                                        ],
                                                        'name' => 'value',
                                                    ],
                                                ],
                                                [
                                                    'class' => EditableColumn::className(),
                                                    'attribute' => 'slug',
                                                    'editableOptions' => [
                                                        'formOptions' => [
                                                            'action' => 'update-set-value',
                                                        ],
                                                        'name' => 'slug',
                                                    ],
                                                ],
                                                'sort_order',
                                                [
                                                    'class' => EditableColumn::className(),
                                                    'attribute' => 'display',
                                                    'value' => function ($model) {
                                                        return var_export($model['display'], true);
                                                    },
                                                    'editableOptions' => [
                                                        'inputType' => Editable::INPUT_SWITCH,
                                                        'formOptions' => [
                                                            'action' => 'update-set-value',
                                                        ],
                                                        'name' => 'display',
                                                    ],
                                                ],
                                            ]
                                        ]
                                    );
                                    return Html::tag(
                                        'tr',
                                        Html::tag('td', $gridForFilterValues, ['colspan' => count($grid->columns)]),
                                        ['data' => ['for' => $index], 'class' => 'filter-values']
                                    );
                                },
                                'tableOptions' => [
                                    'class' => 'table table-bordered table-hover table-responsive',
                                ],
                            ]
                        ) ?>
                        <?php Pjax::end() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJs(
    <<<JS
$('.filter-values').hide();
$('#filters-grid-container').on('click', '.toggle-filter-values', function (e) {    
    $('.filter-values[data-for=' + $(e.target).data('for') + ']').slideToggle();
    return false;
});
JS
);
