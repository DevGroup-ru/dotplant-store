<?php
/**
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var  \DotPlant\EntityStructure\models\BaseStructure $searchModel
 * @var int $parentId
 */
use DevGroup\AdminUtils\columns\ActionColumn;
use yii\grid\GridView;
use kartik\icons\Icon;
use yii\helpers\Html;
use DevGroup\AdminUtils\Helper;
use DotPlant\Store\models\goods\Goods;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use yii\helpers\Url;

$this->title = Yii::t('dotplant.store', 'Goods');
$this->params['breadcrumbs'][] = [
    'url' => ['/structure/entity-manage/index'],
    'label' => Yii::t('dotplant.store', 'Goods category management')
];
$this->params['breadcrumbs'][] = $this->title;
$types = Goods::getTypes();
$returnUrl = Helper::returnUrl();
$links = [];
foreach ($types as $type => $name) {
    $links[] = Html::a(
        $name,
        ['/structure/entity-manage/goods-manage', 'type' => $type, 'returnUrl' => $returnUrl]
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
        <div class="col-sm-4">
            <div class="btn-group pull-left" style="margin: 20px 0;">
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
        <div class="col-sm-8">
            {pager}
        </div>
    </div>
</div>
HTML;
?>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <div class="box">
            <div class="box-body">
                //Categories path
            </div>
        </div>
        <div class="goods__list box box-solid">
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
                        'attribute' => 'type',
                        'content' => function ($data) use ($types) {
                            return Yii::t('dotplant.store', $types[$data->type]);
                        },
                        'filter' => $types,
                    ],
                    'role',
                    [
                        'attribute' => 'slug',
                        'options' => [
                            'width' => '15%',
                        ],
                    ],
                    'is_active:boolean',
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
                        'buttons' => function ($model, $key, $index, $column) {

                            $result = [
                                'edit' => [
                                    'url' => '/structure/entity-manage/goods-manage',
                                    'icon' => 'pencil',
                                    'class' => 'btn-info',
                                    'label' => Yii::t('dotplant.store', 'Edit'),
                                    'keyParam' => 'product_id',
                                ]
                            ];

                            if ($model->isDeleted() === false) {
                                $result['soft-delete'] = [
                                    'url' => '/structure/entity-manage/goods-delete',
                                    'icon' => 'trash-o',
                                    'class' => 'btn-danger',
                                    'label' => Yii::t('dotplant.store', 'Delete'),
                                    'keyParam' => 'product_id',
                                ];
                            } else {
                                $result['restore'] = [
                                    'url' => '/structure/entity-manage/goods-restore',
                                    'icon' => 'undo',
                                    'class' => 'btn-info',
                                    'label' => Yii::t('dotplant.store', 'Restore'),
                                    'keyParam' => 'product_id',
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
