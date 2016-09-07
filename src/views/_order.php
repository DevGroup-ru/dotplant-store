<?php

/**
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var \yii\web\View $this
 */

$statuses = \DotPlant\Store\models\order\OrderStatus::find()->select(['name', 'id'])->asArray(true)->indexBy('id')->column();

?>
<div class="row">
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
    <div class="col-xs-12 col-md-6"></div>
</div>
