<?php
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\price\Price;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use kartik\select2\Select2;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

/**
 * @var GoodsWarehouse[] $prices
 * @var Goods $goods
 * @var int $goods_id
 * @var array $values
 */
$url = \yii\helpers\Url::to(
    [
        '/structure/entity-manage/goods-autocomplete'
    ]
);
$this->title = \Yii::t('dotplant.store', 'Add new item');
?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-md-10">
                <?php
                $form = ActiveForm::begin([
                    'method' => 'get',
                    'action' => ['/store/orders-manage/add-item', 'order_id' => $order_id]
                ]);

                echo Select2::widget(
                    [
                        'name' => 'goods_id',
                        'data' => $values,
                        'value' => $goods_id,
                        'options' => [
                            'placeholder' => Yii::t('dotplant.store', 'Search for a new order item ...'),
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
                ?>
            </div>
            <div class="col-md-2">
                <?php
                echo Html::submitButton(Yii::t('dotplant.store', 'Select goods'), ['class' => 'btn btn-primary']);
                $form->end();
                ?>


            </div>
            <div class="col-md-12">
                <table class="table">
                    <tr>
                        <td><?= Yii::t('dotplant.store', 'Warehouse') ?></td>
                        <td><?= Yii::t('dotplant.store', 'Price') ?></td>
                        <td><?= Yii::t('dotplant.store', 'Quantity') ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($prices as $priceWarehouse): ?>
                        <?php
                        $price = $priceWarehouse->getPrice(Price::TYPE_RETAIL);
                        ?>
                        <tr>
                            <?php $form = ActiveForm::begin(); ?>
                            <td><?= $priceWarehouse->warehouse->name; ?></td>
                            <td><?= $price['value'] ?> <?= $price['isoCode'] ?></td>
                            <td><?= Html::textInput('quantity', 1) ?> </td>
                            <td>
                                <?= Html::hiddenInput('warehouse_id', $priceWarehouse->warehouse_id) ?>
                                <?= Html::submitButton(
                                    Yii::t('dotplant.store', 'Add to order')
                                ) ?>
                            </td>
                            <?php $form->end(); ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>