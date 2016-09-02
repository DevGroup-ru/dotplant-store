<?php
/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var  \DotPlant\EntityStructure\models\BaseStructure $searchModel
 * @var int $parentId
 */
use yii\grid\GridView;
use kartik\icons\Icon;
use yii\helpers\Html;
use DevGroup\AdminUtils\Helper;
use DotPlant\Store\models\goods\Goods;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use yii\helpers\Url;

$this->title = Yii::t('dotplant.store', 'Goods');
$this->params['breadcrumbs'][] = $this->title;
$types = Goods::getTypes();
$returnUrl = Helper::returnUrl();
$links = [];
foreach ($types as $type => $name) {
    $links[] = Html::a(
        $name,
        ['/store/goods-manage/edit', 'type' => $type, 'returnUrl' => $returnUrl]
    );
}
$ul = Html::ul($links, ['encode' => false, 'class' => 'dropdown-menu', 'role' => 'menu']);
$newText = Yii::t('dotplant.store', 'New');
$buttons = '';
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
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        $newText
                        <span class="caret"></span>
                    </button>
                    $ul
                </div>
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
            'treeDataRoute' => ['/content/pages-manage/get-tree', 'selected_id' => 0],
            'reorderAction' => ['/content/pages-manage/tree-reorder'],
            'changeParentAction' => ['/content/pages-manage/tree-parent'],
            'treeType' => TreeWidget::TREE_TYPE_ADJACENCY,
            'contextMenuItems' => [
                'open' => [
                    'label' => 'Open',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/content/pages-manage/index'],
                        ['parent_id', 'context_id', 'id']
                    ),
                ],
                'edit' => [
                    'label' => 'Edit',
                    'action' => ContextMenuHelper::actionUrl(
                        ['/content/pages-manage/edit']
                    ),
                ]
            ],
        ]) ?>
    </div>
    <div class="col-sm-12 col-md-6">
        <div class="indreams-pages__list-pages box box-solid">
            <div class="box-header with-border clearfix">
                <h3 class="box-title pull-left">
                    <?= Yii::t('dotplant.store', 'Goods list') ?>
                </h3>
            </div>
            <?= GridView::widget([
                'id' => 'goods-list',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'layout' => $gridTpl,
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover table-responsive',
                ],
                'columns' => [
                    [
                        'attribute' => 'name',
                        'options' => [
                            'width' => '20%',
                        ],
                    ],
                    [
                        'attribute' => 'title',
                        'options' => [
                            'width' => '20%',
                        ],
                    ],
                    [
                        'attribute' => 'slug',
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
                        'class' => 'DevGroup\AdminUtils\columns\ActionColumn',
                        'options' => [
                            'width' => '95px',
                        ],
                        'buttons' => [
                            [
                                'url' => 'edit',
                                'icon' => 'pencil',
                                'class' => 'btn-info',
                                'label' => Yii::t('dotplant.store', 'Edit'),
                            ],
                            [
                                'url' => 'delete',
                                'icon' => 'trash-o',
                                'class' => 'btn-danger',
                                'label' => Yii::t('dotplant.store', 'Delete'),
                            ],
                        ],
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
