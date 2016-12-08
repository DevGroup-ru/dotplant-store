<?php

use devgroup\jsoneditor\Jsoneditor;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use DotPlant\Store\models\warehouse\Warehouse;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var bool $hasAccess
 * @var DotPlant\Store\models\warehouse\Warehouse $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Warehouses'), 'url' => ['index']],
    $this->title,
];
$contexts = call_user_func([Yii::$app->multilingual->modelsMap['Context'], 'getListData']);

?>
<?php $form = ActiveForm::begin([]); ?>
    <div class="box">
        <div class="box-body">
            <?=
            $form->field($model, 'context_id')
                ->dropDownList($contexts)
            ?>
            <?=
            $form
                ->field($model, 'type')
                ->dropDownList(Warehouse::getTypes())
            ?>
            <?= $form->field($model, 'handler_class') ?>
            <?= $form->field($model, 'packed_json_params')->widget(Jsoneditor::class) ?>
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
        <?php if ($hasAccess) : ?>
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
        <?php endif; ?>
    </div>
<?php ActiveForm::end();
