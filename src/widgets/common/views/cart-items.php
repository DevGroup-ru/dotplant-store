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
<table class="table table-striped table-condensed table-bordered">
    <thead>
    <tr>
        <th><?= Yii::t('dotplant.store', 'Name') ?></th>
        <th><?= Yii::t('dotplant.store', 'Quantity') ?></th>
        <th><?= Yii::t('dotplant.store', 'Price with discount') ?></th>
        <th><?= Yii::t('dotplant.store', 'Price without discount') ?></th>
        <th></th>
    </tr>
    </thead>
    <?php foreach ($items as $item) : ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td>
                <form action="<?= \yii\helpers\Url::toRoute(['/store/cart/change-quantity']) ?>" data-action="ajax-tester">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>" />
                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" />
                    <input type="submit" value="Save" />
                </form>
            </td>
            <td><?= $item['total_price_with_discount'] ?></td>
            <td><?= $item['total_price_without_discount'] ?></td>
            <td>
                <form action="<?= \yii\helpers\Url::toRoute(['/store/cart/remove']) ?>" data-action="ajax-tester">
                    <input type="hidden" name="id" value="<?= $item['id'] ?>" />
                    <input type="submit" value="Remove" />
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <tr class="warning">
        <th><?= Yii::t('dotplant.store', 'Summary') ?></th>
        <th><?= $model->items_count ?></th>
        <th><?= CurrencyHelper::format($model->total_price_with_discount, $currency) ?></th>
        <th><?= CurrencyHelper::format($model->total_price_without_discount, $currency) ?></th>
        <th></th>
    </tr>
</table>
