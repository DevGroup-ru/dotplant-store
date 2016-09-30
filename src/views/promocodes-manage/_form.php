<?php

use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model DotPlant\Store\models\order\Promocode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="promocode-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'is_active')->widget(SwitchInput::class) ?>
    <?php if ($model->isNewRecord): ?>
        <?= $form->field($model, 'promocode_string')->textInput(['maxlength' => true]) ?>
    <?php endif; ?>
    <?= $form->field($model, 'is_unlimited')->widget(SwitchInput::class) ?>

    <?= $form->field($model, 'available_count')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(
            $model->isNewRecord ? Yii::t('dotplant.store', 'Create') : Yii::t('dotplant.store', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
