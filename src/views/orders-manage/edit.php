<?php

use DevGroup\DataStructure\widgets\PropertiesForm;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\OrderStatus;
use DotPlant\Store\models\order\Payment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var bool $hasAccess
 * @var DotPlant\Store\models\order\Order $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Orders'), 'url' => ['index', 'contextId' => $model->context_id]],
    Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update'),
];

?>
<?php $form = ActiveForm::begin(); ?>
<div class="box">
    <div class="box-body">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#order-data" data-toggle="tab" aria-expanded="true">
                        <?= Yii::t('dotplant.entity.structure', 'Main options') ?>
                    </a>
                </li>
                <?php if (false === $model->isNewRecord) : ?>
                    <li class="">
                        <a href="#order-properties" data-toggle="tab" aria-expanded="false">
                            <?= Yii::t('dotplant.entity.structure', 'Properties') ?>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="order-data">
                    <div class="row">
                        <div class="col-xs-12 col-md-3">
                            <?= $form->field($model, 'status_id')->dropDownList(OrderStatus::listData($model->context_id)) ?>
                            <?= $form->field($model, 'delivery_id')->dropDownList(Delivery::listData($model->context_id)) ?>
                            <?= $form->field($model, 'payment_id')->dropDownList(Payment::listData($model->context_id)) ?>
                            <?=
                            $form
                                ->field($model, 'manager_id')
                                ->dropDownList(
                                    [null => Yii::t('dotplant.store', 'Select the option')]
                                    + \DotPlant\Store\helpers\BackendHelper::managersDropDownList()
                                )
                            ?>
                        </div>
                        <div class="col-xs-12 col-md-3">
                            <?= $form->field($model, 'is_retail')->textInput(['readonly' => 'readonly']) ?>
                            <?= $form->field($model, 'promocode_id')->textInput(['readonly' => 'readonly']) ?>
                            <?= $form->field($model, 'promocode_discount')->textInput(['readonly' => 'readonly']) ?>
                            <?= $form->field($model, 'promocode_name')->textInput(['readonly' => 'readonly']) ?>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <?= $form->field($model->deliveryInformation, 'full_name')->textInput() ?>
                            <?= $form->field($model->deliveryInformation, 'email')->textInput() ?>
                            <?= $form->field($model->deliveryInformation, 'phone')->textInput() ?>
                        </div>
                        <div class="col-xs-12 col-md-2">
                            <?= $form->field($model->deliveryInformation, 'zip_code')->textInput() ?>
                            <?= $form->field($model->deliveryInformation, 'address')->textarea(['rows' => 6]) ?>
                        </div>
                        <?php if (!empty($model->deliveryInformation)) : ?>
                        <?php endif; ?>
                        <div class="col-xs-12 col-md-2">
                            <?=
                            \DevGroup\Entity\widgets\BaseActionsInfoWidget::widget(
                                [
                                    'model' => $model,
                                ]
                            )
                            ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-md-8">
                            <?=
                            \DotPlant\Store\widgets\common\OrderItems::widget(
                                [
                                    'model' => $model,
                                ]
                            )
                            ?>
                        </div>
                        <div class="col-xs-12 col-md-4">
                            <p>There will be managers chat here</p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="order-properties">
                    <?= PropertiesForm::widget([
                        'model' => $model,
                        'form' => $form,
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <?php if ($hasAccess) : ?>
    <div class="box-footer">
        <div class="pull-right">
            <?=
            Html::submitButton(
                Yii::t('dotplant.store', $model->isNewRecord ? 'Create' : 'Update'),
                ['class' => 'btn btn-primary']
            )
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php ActiveForm::end();
