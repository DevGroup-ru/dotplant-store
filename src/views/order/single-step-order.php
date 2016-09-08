<?php

/**
 * @var \DotPlant\Store\models\order\Order $order
 * @var \DotPlant\Store\models\order\OrderDeliveryInformation $orderDeliveryInformation
 * @var yii\web\View $this
 */

use yii\bootstrap\ActiveForm;

?>
<?php $form = ActiveForm::begin() ?>
<h1>Create an order</h1>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="box">
            <div class="box-body">
                <?= $form->field($orderDeliveryInformation, 'full_name') ?>
                <?= $form->field($orderDeliveryInformation, 'email') ?>
                <?= $form->field($orderDeliveryInformation, 'phone') ?>
                <?= $form->field($orderDeliveryInformation, 'zip_code') ?>
                <?= $form->field($orderDeliveryInformation, 'address')->textarea() ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <div class="box">
            <div class="box-body">
                <?= $form->field($order, 'payment_id')->dropDownList(\DotPlant\Store\models\order\Payment::listData()) ?>
                <?= $form->field($order, 'delivery_id')->dropDownList(\DotPlant\Store\models\order\Delivery::listData()) ?>
            </div>
        </div>
    </div>
</div>
<?= \yii\bootstrap\Html::submitButton('Create', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
