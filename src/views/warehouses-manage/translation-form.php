<?php
/**
 * @var string $attributePrefix
 * @var \yii\bootstrap\ActiveForm $form
 * @var \DotPlant\Store\models\Warehouse $model
 * @var \yii\web\View $this
 */
?>
<?= $form->field($model, $attributePrefix . 'name') ?>
<?=
$form
    ->field($model, $attributePrefix . 'address')
    ->textarea();
