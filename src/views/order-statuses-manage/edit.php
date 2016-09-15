<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var DotPlant\Store\models\order\OrderStatus $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Order Statuses'), 'url' => ['index', 'contextId' => $model->context_id]],
    Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update'),
];

?>
<?php $form = ActiveForm::begin(); ?>
<div class="box">
    <div class="box-body">
        <?= $form->field($model, 'context_id')->textInput(['readonly' => 'readonly']) ?>
        <?= $form->field($model, 'label_class')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'sort_order')->textInput(['type' => 'number']) ?>
        <?= $form->field($model, 'is_active')->widget(\kartik\switchinput\SwitchInput::class) ?>
        <?=
        DevGroup\Multilingual\widgets\MultilingualFormTabs::widget(
            [
                'model' => $model,
                'childView' => '@DotPlant/Store/views/order-statuses-manage/translation-form.php',
                'form' => $form,
            ]
        )
        ?>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?=
            Html::submitButton(
                Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update'),
                ['class' => 'btn btn-primary'])
            ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
