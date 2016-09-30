<?php

/**
 * @var int $orderId
 * @var \yii\web\View $this
 */

use DotPlant\Store\models\order\Order;

$model = Order::findOne($orderId);

?>
<h1>New order #<?= $orderId ?></h1>

<h3>Order info</h3>
<table style="width: 100%;">
    <tr style="background-color: #f4f4f4">
        <th><?= $model->getAttributeLabel('payment_id') ?></th>
        <td><?= isset($model->payment->translations[0]) ? $model->payment->translations[0]->name : '' ?></td>
    </tr>
    <tr>
        <th><?= $model->getAttributeLabel('delivery_id') ?></th>
        <td><?= $model->delivery !== null ? $model->delivery->translations[0]->name : '' ?></td>
    </tr>
    <tr style="background-color: #f4f4f4">
        <th><?= $model->getAttributeLabel('status_id') ?></th>
        <td><?= $model->status !== null ? $model->status->translations[0]->name : '' ?></td>
    </tr>
</table>

<h3>Delivery information</h3>
<table style="width: 100%;">
    <?php if ($model->deliveryInformation !== null) : ?>
        <?php foreach (['full_name', 'email', 'phone', 'zip_code', 'address'] as $i => $attributeName) : ?>
            <tr style="background-color: <?= $i % 2 === 0 ? '#f4f4f4' : '#ffffff' ?>">
                <th><?= $model->deliveryInformation->getAttributeLabel($attributeName) ?></th>
                <td><?= $model->deliveryInformation->{$attributeName}?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<h3>Order items</h3>
<?=
\DotPlant\Store\widgets\common\OrderItems::widget(
    [
        'model' => $model,
        'viewFile' => 'email-order-items',
    ]
);
