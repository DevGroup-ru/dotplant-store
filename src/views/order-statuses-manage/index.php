<?php

/**
 * @var int $contextId
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $tabs
 * @var yii\web\View $this
 */

use DevGroup\AdminUtils\columns\ActionColumn;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = Yii::t('dotplant.store', 'Order Statuses');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="box">
    <div class="box-body">
        <?=
        Tabs::widget(
            [
                'items' => $tabs,
            ]
        )
        ?>
        <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                'id',
                [
                    'attribute' => 'smartTranslation.label',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        if ($model->smartTranslation === null) {
                            return null;
                        }
                        return Html::tag('span', $model->smartTranslation->label, ['class' => $model->label_class]);
                    },
                ],
                'smartTranslation.name',
                'sort_order',
                'is_active',
                ['class' => ActionColumn::class],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?=
            Html::a(
                Yii::t('dotplant.store', 'Create'),
                ['edit', 'contextId' => $contextId],
                ['class' => 'btn btn-success']
            )
            ?>
        </div>
    </div>
</div>
