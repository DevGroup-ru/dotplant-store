<?php

/**
 * @var int $oldManagerId
 * @var int $managerId
 * @var int $orderId
 * @var string[] $managers
 * @var \yii\web\View $this
 */

$order = \DotPlant\Store\models\order\Order::findOne($orderId);

?>
<h1>You are attached to the order #<?= $orderId ?> as a manager</h1>

<?=
\DotPlant\Store\widgets\common\OrderItems::widget(
    [
        'model' => $order,
        'viewFile' => 'email-order-items',
    ]
)
?>

<p>You can see more details about this order at the admin panel.</p>
