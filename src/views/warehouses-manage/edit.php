<?php

use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var DotPlant\Store\models\Warehouse $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Warehouses'), 'url' => ['index']],
    $this->title,
];

?>
<?php $form = ActiveForm::begin([]); ?>
<div class="box">
    <div class="box-body">
        <?= $form->field($model, 'priority') ?>
        <?=
        DevGroup\Multilingual\widgets\MultilingualFormTabs::widget(
            [
                'model' => $model,
                'childView' => '@DotPlant/Store/views/warehouses-manage/translation-form.php',
                'form' => $form,
            ]
        )
        ?>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <?=
            Html::submitButton(
                Yii::t('dotplant.store', 'Save'),
                ['class' => 'btn btn-primary']
            )
            ?>
        </div>
    </div>
</div>
<?php ActiveForm::end();
