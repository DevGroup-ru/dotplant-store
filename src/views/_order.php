<?php

/**
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var \yii\web\View $this
 */

$statuses = \DotPlant\Store\models\order\OrderStatus::listData();

use kartik\switchinput\SwitchInput;

?>
<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="box">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('dotplant.store', 'Cart') ?></h3></div>
            <div class="box-body">
                <?= $form->field($model, 'allowToAddSameGoods')->widget(SwitchInput::class) ?>
                <?= $form->field($model, 'countUniqueItemsOnly')->widget(SwitchInput::class) ?>
                <?= $form->field($model, 'singlePriceForWarehouses')->widget(SwitchInput::class) ?>
                <?= $form->field($model, 'registerGuestInCart')->widget(SwitchInput::class) ?>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <div class="box">
            <div class="box-header with-border"><h3 class="box-title"><?= Yii::t('dotplant.store', 'Order statuses') ?></h3></div>
            <div class="box-body">
                <?= $form->field($model, 'newOrderStatusId')->dropDownList($statuses) ?>
                <?= $form->field($model, 'paidOrderStatusId')->dropDownList($statuses) ?>
                <?= $form->field($model, 'doneOrderStatusId')->dropDownList($statuses) ?>
                <?= $form->field($model, 'canceledOrderStatusId')->dropDownList($statuses) ?>
            </div>
        </div>
    </div>
</div>
