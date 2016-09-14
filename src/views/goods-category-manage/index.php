<?php
/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var  \DotPlant\EntityStructure\models\BaseStructure $searchModel
 * @var int $parentId
 */
use yii\grid\GridView;
use kartik\icons\Icon;
use yii\helpers\Html;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use \devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use DevGroup\AdminUtils\Helper;
use \DevGroup\AdminUtils\columns\ActionColumn;

$this->title = Yii::t('dotplant.store', 'Goods categories');
$this->params['breadcrumbs'][] = $this->title;
$buttons = Html::a(
    Icon::show('plus') . '&nbsp'
    . Yii::t('dotplant.store', 'New category'),
    ['/store/goods-category-manage/edit', 'parent_id' => $parentId, 'returnUrl' => Helper::returnUrl()],
    [
        'class' => 'btn btn-success',
    ]);
$gridTpl = <<<HTML
<div class="box-body">
    {summary}
    {items}
</div>
<div class="box-footer">
    <div class="row list-bottom">
        <div class="col-sm-5">
            {pager}
        </div>
        <div class="col-sm-7">
            <div class="btn-group pull-right" style="margin: 20px 0;">
                $buttons
            </div>
        </div>
    </div>
</div>
HTML;
?>
<div class="row">
    <div class="col-sm-12 col-md-6">
        <?= TreeWidget::widget([
            'treeDataRoute' => ['/store/goods-category-manage/get-tree', 'selected_id' => $parentId],
            'reorderAction' => ['/store/goods-category-manage/tree-reorder'],
            'changeParentAction' => ['/store/goods-category-manage/tree-parent'],
            'treeType' => TreeWidget::TREE_TYPE_ADJACENCY,
            'contextMenuItems' => [
                'open' => [
                    'label' => 'Open',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/store/goods-category-manage/index'],
                        ['parent_id', 'context_id', 'id']
                    ),
                ],
                'edit' => [
                    'label' => 'Edit',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/store/goods-category-manage/edit']
                    ),
                ]
            ],
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="indreams-pages__list-pages box box-solid">
            <div class="box-header with-border clearfix">
                <h3 class="box-title pull-left">
                    <?= Yii::t('dotplant.store', 'Categories list') ?>
                </h3>
            </div>
            <?php
            echo GridView::widget([
                'id' => 'pages-list',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => $gridTpl,
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover table-responsive',
                ],
                'columns' => [
                    [
                        'attribute' => 'name',
                        'label' => Yii::t('dotplant.entity.structure', 'Name'),
                        'options' => [
                            'width' => '20%',
                        ],
                    ],
                    [
                        'attribute' => 'title',
                        'label' => Yii::t('entity', 'Title'),
                        'options' => [
                            'width' => '20%',
                        ],
                    ],
                    [
                        'attribute' => 'slug',
                        'label' => Yii::t('entity', 'Last url part'),
                        'options' => [
                            'width' => '15%',
                        ],
                    ],
                    [
                        'attribute' => 'is_active',
                        'label' => Yii::t('dotplant.store', 'Active'),
                        'content' => function ($data) {
                            return Yii::$app->formatter->asBoolean($data->is_active);
                        },
                        'filter' => [
                            0 => Yii::$app->formatter->asBoolean(0),
                            1 => Yii::$app->formatter->asBoolean(1),
                        ],
                    ],
                    [
                        'attribute' => 'is_deleted',
                        'label' => Yii::t('dotplant.store', 'Show deleted?'),
                        'value' => function ($model) {
                            return $model->isDeleted() === true ? Yii::t('dotplant.store', 'Deleted') : Yii::t('dotplant.store', 'Active');
                        },
                        'filter' => [
                            Yii::t('dotplant.store', 'Show only active'),
                            Yii::t('dotplant.store', 'Show only deleted')
                        ],
                        'filterInputOptions' => [
                            'class' => 'form-control',
                            'id' => null,
                            'prompt' => Yii::t('dotplant.store', 'Show all')
                        ]
                    ],
                    [
                        'class' => ActionColumn::class,
                        'options' => [
                            'width' => '120px',
                        ],
                        'buttons' => function ($model, $key, $index, $column) {
                            $result = [
                                [
                                    'url' => '/store/goods-category-manage/edit',
                                    'icon' => 'pencil',
                                    'class' => 'btn-primary',
                                    'label' => Yii::t('dotplant.store', 'Edit'),
                                ],
                            ];

                            if ($model->isDeleted() === false) {
                                $result['delete'] = [
                                    'url' => '/store/goods-category-manage/delete',
                                    'visible' => false,
                                    'icon' => 'trash-o',
                                    'class' => 'btn-warning',
                                    'label' => Yii::t('dotplant.store', 'Delete'),
                                    'options' => [
                                        'data-action' => 'delete',
                                        'data-method' => 'post',
                                    ],
                                ];
                            } else {
                                $result['restore'] = [
                                    'url' => '/store/goods-category-manage/restore',
                                    'icon' => 'undo',
                                    'class' => 'btn-info',
                                    'label' => Yii::t('dotplant.store', 'Restore'),
                                ];
                                $result['delete'] = [
                                    'url' => '/store/goods-category-manage/delete',
                                    'urlAppend' => ['hard' => 1],
                                    'icon' => 'trash-o',
                                    'class' => 'btn-danger',
                                    'label' => Yii::t('dotplant.store', 'Delete'),
                                    'options' => [
                                        'data-action' => 'delete',
                                        'data-method' => 'post',
                                    ],
                                ];
                            }

                            return $result;
                        }
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
