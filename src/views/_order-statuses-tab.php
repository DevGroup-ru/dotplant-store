<?php

/**
 * @var \DevGroup\Multilingual\models\Context $context
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var \yii\web\View $this
 */

$statuses = \DotPlant\Store\models\order\OrderStatus::listData($context->id);

?>
<?= $form->field($model, 'newOrderStatusId[' . $context->id . ']')->dropDownList($statuses) ?>
<?= $form->field($model, 'paidOrderStatusId[' . $context->id . ']')->dropDownList($statuses) ?>
<?= $form->field($model, 'doneOrderStatusId[' . $context->id . ']')->dropDownList($statuses) ?>
<?= $form->field($model, 'canceledOrderStatusId[' . $context->id . ']')->dropDownList($statuses);
