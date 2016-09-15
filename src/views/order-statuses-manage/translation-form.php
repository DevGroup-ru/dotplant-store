<?php
/**
 * @var string $attributePrefix
 * @var \yii\bootstrap\ActiveForm $form
 * @var \DotPlant\Store\models\order\OrderStatus $model
 * @var \yii\web\View $this
 */
?>
<?= $form->field($model, $attributePrefix . 'label') ?>
<?= $form->field($model, $attributePrefix . 'name') ?>
<?=
$form
    ->field($model, $attributePrefix . 'description')
    ->textarea();
