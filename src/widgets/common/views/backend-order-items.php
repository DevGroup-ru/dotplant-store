<?php

/**
 * @var array $delivery
 * @var array[] $items
 * @var \DotPlant\Store\models\order\Order $model
 * @var \yii\web\View $this
 */

use DevGroup\AdminUtils\Helper;
use DotPlant\Currencies\helpers\CurrencyHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$currency = CurrencyHelper::findCurrencyByIso($model->currency_iso_code);

?>

<?php if (Yii::$app->session->hasFlash('orderItemsBlock')): ?>
    <div class="callout callout-info">
        <?= Yii::$app->session->getFlash('orderItemsBlock') ?>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin(['action' => ['/store/orders-manage/edit-items', 'id' => $model->id]]); ?>
    <table class="table table-bordered table-condensed table-striped">
        <thead>
        <tr>
            <th></th>
            <th><?= Yii::t('dotplant.store', 'Name') ?></th>
            <th><?= Yii::t('dotplant.store', 'Quantity') ?></th>
            <th><?= Yii::t('dotplant.store', 'Price with discount') ?></th>
            <th><?= Yii::t('dotplant.store', 'Price without discount') ?></th>
            <th><?= Yii::t('dotplant.store', 'Action') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item) : ?>
            <tr>
                <td><?= Html::checkbox('id[]', false, ['value' => $item['id']]); ?></td>
                <td><?= $item['name'] ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= CurrencyHelper::format($item['total_price_with_discount'], $currency) ?></td>
                <td><?= CurrencyHelper::format($item['total_price_without_discount'], $currency) ?></td>
                <td><?=
                    Html::a(
                        Yii::t('dotplant.store', 'Remove from order'),
                        [
                            '/store/orders-manage/remove-item',
                            'order_id' => $model->id,
                            'item_id' => $item['id'],
                            'returnUrl' => Helper::returnUrl()
                        ]
                    )
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($delivery !== null) : ?>
            <tr>
                <td colspan="3"><?= Yii::t('dotplant.store', 'Delivery') ?>: <?= $delivery['name'] ?></td>
                <td><?= CurrencyHelper::format($delivery['total_price_with_discount'], $currency) ?></td>
                <td><?= CurrencyHelper::format($delivery['total_price_without_discount'], $currency) ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        <tr class="warning">
            <th colspan="2"><?= Yii::t('dotplant.store', 'Summary') ?></th>
            <th><?= $model->items_count ?></th>
            <th><?= CurrencyHelper::format($model->total_price_with_discount, $currency) ?></th>
            <th><?= CurrencyHelper::format($model->total_price_without_discount, $currency) ?></th>
            <th></th>
        </tr>

        </tbody>
    </table>
<?php if ($model->transactions() == []) : ?>
    <?= Html::submitButton(
        Yii::t('dotplant.store', 'Move to new order'),
        [
            'name' => 'action',
            'value' => 'move_to_new_order',
            'class' => 'btn btn-warning'
        ]
    ); ?>
    <?= Html::a(
        Yii::t('dotplant.store', 'Add new item'),
        ['/store/orders-manage/add-item', 'order_id' => $model->id],
        [
            'class' => 'btn btn-primary'
        ]
    ) ?>
<?php endif; ?>
<?php $form->end(); ?>