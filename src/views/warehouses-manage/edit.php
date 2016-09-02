<?php

use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use DotPlant\Store\models\warehouse\Warehouse;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var DotPlant\Store\models\warehouse\Warehouse $model
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
        $form
            ->field($model, 'type')
            ->dropDownList(Warehouse::getTypes())
        ?>
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