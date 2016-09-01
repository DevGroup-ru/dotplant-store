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

?>
<div class="box">
    <div class="box-body">
        <?php Pjax::begin(); ?>
        <?=
        GridView::widget(
            [
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    'name',
                    'priority',
                    [
                        'class' => \DevGroup\AdminUtils\columns\ActionColumn::class,
                    ],
                ],
            ]
        )
        ?>
        <?php Pjax::end() ?>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?= Html::a(Yii::t('dotplant.store', 'Create'), ['edit'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
