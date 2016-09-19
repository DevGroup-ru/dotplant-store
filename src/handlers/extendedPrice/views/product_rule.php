<?php
use DotPlant\Store\handlers\extendedPrice\UserRule;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/**
 * @var ActiveForm $form
 * @var UserRule $model ,
 * @var ExtendedPriceRule $rule
 * @var array $products
 */

echo $form->field($rule, '['.$rule->id.']params[products]')->widget(
    Select2::class,
    [
        'options' => ['multiple' => true, 'placeholder' => 'Select states ...'],
        'data' => $products,
    ]
);
