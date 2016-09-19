<?php
use DevGroup\AdminUtils\columns\ActionColumn;
use kartik\icons\Icon;
use yii\grid\DataColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Extended price');

$this->params['breadcrumbs'][] = $this->title;
$buttons = Yii::$app->user->can('store-extended-price-create') ?
    Html::a(
        Icon::show('plus') . '&nbsp'
        . Yii::t('app', 'New item'),
        ['/store/extended-price-manage/edit', 'returnUrl' => \DevGroup\AdminUtils\Helper::returnUrl()],
        ['class' => 'btn btn-success']
    ) : '';
$gridTpl = <<<TPL
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
TPL;


?>
<div class="box">
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => $gridTpl,
        'summaryOptions' => ['class' => 'summary col-md-12 dataTables_info'],
        'tableOptions' => [
            'class' => 'table table-bordered table-hover table-responsive dataTable',
        ],
        'columns' => [
            [
                'class' => DataColumn::class,
                'attribute' => 'id',
            ],
            'name',
            'value',
            'mode',
            'currency_iso_code',
            'min_product_price',
            'start_time:datetime',
            'end_time:datetime',
            'context_id',
            'is_final:boolean',
            'calculator_type',
            'target_class',
            'priority',
            [
                'class' => ActionColumn::class,
                'buttons' => function ($model, $key, $index, $column) {
                    return [
                        'edit' => [
                            'url' => '/store/extended-price-manage/edit',
                            'icon' => 'pencil',
                            'class' => 'btn-primary',
                            'attrs' => ['parent_id'],
                            'label' => Yii::t('app', 'Edit'),
                        ],
                        'delete' => [
                            'url' => '/store/extended-price-manage/delete',
                            'icon' => 'trash-o',
                            'class' => 'btn-warning',
                            'label' => Yii::t('app', 'Delete'),
                            'options' => [
                                'data-action' => 'delete',
                            ],
                        ]
                    ];
                },
            ],
        ],
    ]);
    ?>
</div>
