<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', 'Warehouses');
$this->params['breadcrumbs'][] = $this->title;
$contexts = call_user_func([Yii::$app->multilingual->modelsMap['Context'], 'getListData']);

?>
<div class="box">
    <div class="box-body">
        <?php Pjax::begin(); ?>
        <?=
        GridView::widget(
            [
                'dataProvider' => $dataProvider,
                'id' => 'warehouses-grid',
                'columns' => [
                    [
                        'class' => \app\modules\admin\widgets\SortColumn::class,
                        'gridContainerId' => 'warehouses-grid',
                    ],
                    'id',
                    [
                        'attribute' => 'context_id',
                        'label' => Yii::t('dotplant.store', 'Context'),
                        'value' => function ($model, $key, $index, $column) use ($contexts) {
                            return isset($contexts[$model->{$column->attribute}])
                                ? $contexts[$model->{$column->attribute}]
                                : Yii::t('dotplant.store', 'Unknown');
                        },
                    ],
                    'name',
                    [
                        'class' => \DevGroup\AdminUtils\columns\ActionColumn::class,
                    ],
                ],
            ]
        )
        ?>
        <?php Pjax::end() ?>
    </div>
    <?php if (Yii::$app->user->can('dotplant-store-warehouse-create')) : ?>
    <div class="box-footer">
        <div class="pull-right">
            <?= Html::a(Yii::t('dotplant.store', 'Create'), ['edit'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
    <?php endif; ?>
</div>
