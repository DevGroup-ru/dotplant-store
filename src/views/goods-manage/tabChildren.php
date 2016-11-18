<?php

use yii\web\JsExpression;
use kartik\select2\Select2;

$url = \yii\helpers\Url::to(
    [
        '/structure/entity-manage/goods-autocomplete',
        'excludedIds' => $goods->id,
        'allowedTypes' => $allowedTypes,
    ]
);

?>
<?=
\yii\grid\GridView::widget(
    [
        'dataProvider' => $childrenDataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'name',
                'options' => [
                    'width' => '80%',
                ],
            ],
            'is_active:boolean',
            [
                'attribute' => 'is_deleted',
                'label' => Yii::t('dotplant.store', 'Show deleted?'),
                'value' => function ($model) {
                    return $model->isDeleted() === true ? Yii::t(
                        'dotplant.store',
                        'Deleted'
                    ) : Yii::t('dotplant.store', 'Active');
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
            'sku',
            [
                'class' => \DevGroup\AdminUtils\columns\ActionColumn::class,
                'buttons' => function ($model, $key, $index, $column) {
                    return [
                        'edit' => [
                            'url' => '/structure/entity-manage/goods-manage',
                            'icon' => 'pencil',
                            'class' => 'btn-info',
                            'label' => Yii::t('dotplant.store', 'Edit'),
                            'keyParam' => 'product_id',
                        ]
                    ];
                }
            ]
        ],
    ]
)
?>
<label><?= Yii::t('dotplant.store', 'Add options or children'); ?></label>
<?=
Select2::widget(
    [
        'name' => 'childGoods',
        'data' => $child,
        'value' => array_keys($child),
        'options' => [
            'placeholder' => Yii::t('dotplant.store', 'Search for a child ...'),
            'multiple' => true
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => $url,
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                'delay' => '700',
                'error' => new JsExpression('function(error) {alert(error.responseText);}'),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(parent) { return parent.text; }'),
            'templateSelection' => new JsExpression('function (parent) { return parent.text; }'),
        ]
    ]
);
