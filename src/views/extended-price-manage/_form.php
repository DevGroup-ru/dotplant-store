<?php
/**
 * @var $this View
 * @var $model ExtendedPrice
 * @var $formAction string
 * @var $additionalFields array
 */

use DevGroup\AdminUtils\FrontendHelper;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\ActiveForm;

?>
<div class="configuration-navigation box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">
            <i class="fa fa-list-alt"></i>

        </h3>
    </div>
    <div class="box-body">


        <?php
        /**
         * @var ActiveForm $form ;
         */
        $form = ActiveForm::begin(
            [
                'action' => $formAction,
                'options' => ['class' => 'extended_price_form', 'data' => ['extra' => $additionalFields]],
            ]
        );
        echo $form->field($model, 'name');
        echo $form->field($model, 'mode')->dropDownList(ExtendedPrice::getModeList());
        echo $form->field($model, 'is_final')->widget(SwitchInput::class);
        echo $form->field($model, 'priority');
        echo $form->field($model, 'value');
        echo $form->field($model, 'currency_iso_code');
        echo $form->field($model, 'min_product_price');
        echo $form->field($model, 'start_time');
        echo $form->field($model, 'end_time');
        echo $form->field($model, 'context_id')->dropDownList(
            ArrayHelper::map(call_user_func([\Yii::$app->multilingual->modelsMap['Context'], 'find'])->all(), 'id', 'name'),
            ['prompt' => '---']
        );
        echo $form->field($model, 'calculator_type')->dropDownList(ExtendedPrice::getCalculatorTypes());
        echo $form->field($model, 'target_class')->dropDownList(ExtendedPrice::getTargetTypes());
        echo FrontendHelper::formSaveButtons(
            $model,
            '/store/extended-price-manage/index'
        );
        $form->end();
        ?>

    </div>
</div>