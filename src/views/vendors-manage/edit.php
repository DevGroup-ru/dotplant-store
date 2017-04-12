<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;

/**
 * @var bool $hasAccess
 * @var DotPlant\Store\models\order\Payment $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Vendors'), 'url' => ['index']],
    $this->title
];

?>
<?php $form = ActiveForm::begin(); ?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $form->field($model, 'name')->textInput() ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= MultilingualFormTabs::widget(
                    [
                        'model' => $model,
                        'childView' => '@DotPlant/Store/views/vendors-manage/_multilingual-part.php',
                        'form' => $form,
                    ]
                )
                ?>
            </div>
        </div>
    </div>
    <?php if ($hasAccess): ?>
    <div class="box-footer">
        <div class="pull-right">
            <?= Html::submitButton(
                Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update'),
                ['class' => 'btn btn-primary']
            ) ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end(); ?>
