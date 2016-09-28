<?php

/**
 * @var array $delivery
 * @var array[] $items
 * @var \DotPlant\Store\models\order\Order $model
 * @var \yii\web\View $this
 */

use DotPlant\Currencies\helpers\CurrencyHelper;

$currency = CurrencyHelper::findCurrencyByIso($model->currency_iso_code);

?>
<table style="width: 100%;" id="order-items" border="0" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th><?= Yii::t('dotplant.store', 'Name') ?></th>
            <th><?= Yii::t('dotplant.store', 'Quantity') ?></th>
            <th><?= Yii::t('dotplant.store', 'Price with discount') ?></th>
            <th><?= Yii::t('dotplant.store', 'Price without discount') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($items as $i => $item) : ?>
        <tr style="background-color: <?= $i % 2 === 0 ? '#f4f4f4' : '#ffffff' ?>">
            <td><?= $item['name'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= CurrencyHelper::format($item['total_price_with_discount'], $currency) ?></td>
            <td><?= CurrencyHelper::format($item['total_price_without_discount'], $currency) ?></td>
        </tr>
        <?php endforeach; ?>
    <?php if ($delivery !== null) : ?>
        <tr>
            <td colspan="2"><?= Yii::t('dotplant.store', 'Delivery') ?>: <?= $delivery['name'] ?></td>
            <td><?= CurrencyHelper::format($delivery['total_price_with_discount'], $currency) ?></td>
            <td><?= CurrencyHelper::format($delivery['total_price_without_discount'], $currency) ?></td>
        </tr>
    <?php endif; ?>
        <tr style="background-color: #fcf8e3">
            <th><?= Yii::t('dotplant.store', 'Summary') ?></th>
            <th><?= $model->items_count ?></th>
            <th><?= CurrencyHelper::format($model->total_price_with_discount, $currency) ?></th>
            <th><?= CurrencyHelper::format($model->total_price_without_discount, $currency) ?></th>
        </tr>
    </tbody>
</table>
<style>
#order-items td, th {border: 1px solid #ddd; padding: 4px 8px;}
</style>
