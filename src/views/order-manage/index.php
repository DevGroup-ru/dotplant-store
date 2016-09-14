<?php

/**
 * @var int $contextId
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var array $tabs
 * @var yii\web\View $this
 */

use DevGroup\AdminUtils\columns\ActionColumn;
use DotPlant\Currencies\helpers\CurrencyHelper;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

$this->title = Yii::t('dotplant.store', 'Orders');
$this->params['breadcrumbs'][] = $this->title;
$deliveryListData = \DotPlant\Store\models\order\Delivery::listData($contextId);
$paymentListData = \DotPlant\Store\models\order\Payment::listData($contextId);

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
                    'attribute' => 'status_id',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) {
                        if (empty($model->{$column->attribute})) {
                            return null;
                        }
                        $status = \DotPlant\Store\models\order\OrderStatus::multilingualFindById($model->{$column->attribute});
                        return $status !== false
                            ? Html::tag('span', $status['label'], ['class' => $status['label_class']])
                            : Yii::t('dotplant.store', 'Unknown');
                    },
                ],
                [
                    'attribute' => 'delivery_id',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) use ($deliveryListData) {
                        if (empty($model->{$column->attribute})) {
                            return null;
                        }
                        return isset($deliveryListData[$model->{$column->attribute}])
                            ? $deliveryListData[$model->{$column->attribute}]
                            : Yii::t('dotplant.store', 'Unknown');
                    },
                ],
                [
                    'attribute' => 'payment_id',
                    'format' => 'raw',
                    'value' => function ($model, $key, $index, $column) use ($paymentListData) {
                        if (empty($model->{$column->attribute})) {
                            return null;
                        }
                        return isset($paymentListData[$model->{$column->attribute}])
                            ? $paymentListData[$model->{$column->attribute}]
                            : Yii::t('dotplant.store', 'Unknown');
                    },
                ],
                 // 'currency_iso_code',
                // 'items_count',
                [
                    'attribute' => 'total_price_with_discount',
                    'value' => function ($model, $key, $index, $column) {
                        return CurrencyHelper::format(
                            $model->{$column->attribute},
                            CurrencyHelper::findCurrencyByIso($model->currency_iso_code)
                        );
                    },
                ],
                [
                    'attribute' => 'total_price_without_discount',
                    'value' => function ($model, $key, $index, $column) {
                        return CurrencyHelper::format(
                            $model->{$column->attribute},
                            CurrencyHelper::findCurrencyByIso($model->currency_iso_code)
                        );
                    },
                ],
                // 'is_retail',
                 'manager_id',
                // 'promocode_id',
                // 'promocode_discount',
                // 'promocode_name',
                // 'rate_to_main_currency',
                // 'created_by',
                // 'created_at',
                // 'updated_by',
                // 'updated_at',
                // 'forming_time:datetime',
                // 'hash',
                ['class' => ActionColumn::class],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?= Html::a(Yii::t('dotplant.store', 'Create'), ['edit', 'contextId' => $contextId], ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>
