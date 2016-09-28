<?php

/**
 * @var int $oldStatusId
 * @var int $statusId
 * @var int $orderId
 * @var string[] $statuses
 * @var \yii\web\View $this
 * @var int $userId
 */

?>
<h1>Order #<?= $orderId ?></h1>

<p>Status has been changed from <?= $statuses[$oldStatusId] ?> to <?= $statuses[$statusId] ?>.</p>
