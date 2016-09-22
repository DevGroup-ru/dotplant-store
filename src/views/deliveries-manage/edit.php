<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var bool $hasAccess
 * @var DotPlant\Store\models\order\Delivery $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create': 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Deliveries'), 'url' => ['index']],
    Yii::t('dotplant.store', $model->isNewRecord ? 'Create': 'Update'),
];
?>
<?php $form = ActiveForm::begin(); ?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $form->field($model, 'context_id')->textInput(['readonly' => 'readonly']) ?>
                <?= $form->field($model, 'handler_class_name')->textInput(['maxlength' => true]) ?>
                <?=
                $form->field($model, 'packed_json_handler_params')
                    ->widget(\devgroup\jsoneditor\Jsoneditor::class)
                ?>
                <?= $form->field($model, 'sort_order')->textInput() ?>
                <?= $form->field($model, 'is_active')->widget(\kartik\switchinput\SwitchInput::class) ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?=
                DevGroup\Multilingual\widgets\MultilingualFormTabs::widget(
                    [
                        'model' => $model,
                        'childView' => '@DotPlant/Store/views/deliveries-manage/translation-form.php',
                        'contextId' => $model->context_id,
                        'form' => $form,
                    ]
                )
                ?>
            </div>
        </div>
    </div>
    <?php if ($hasAccess) : ?>
    <div class="box-footer">
        <div class="pull-right">
            <?=
            Html::submitButton(
                Yii::t('dotplant.store', $model->isNewRecord ? 'Create': 'Update'),
                ['class' => 'btn btn-primary']
            )
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
