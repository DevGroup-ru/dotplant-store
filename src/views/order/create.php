<?php

/**
 * @var \DotPlant\Store\models\order\OrderDeliveryInformation $model
 * @var yii\web\View $this
 */

use yii\bootstrap\ActiveForm;

?>
<h1>Create an order</h1>

<?php $form = ActiveForm::begin() ?>
<?= $form->field($model, 'full_name') ?>
<?= $form->field($model, 'zip_code') ?>
<?= $form->field($model, 'address')->textarea() ?>
<?= \yii\bootstrap\Html::submitButton('Create', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
<?php var_dump($model->errors) ?>