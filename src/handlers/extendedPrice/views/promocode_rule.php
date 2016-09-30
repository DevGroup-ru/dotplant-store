<?php
/**
 * @var yii\widgets\ActiveForm $form
 * @var DotPlant\Store\handlers\extendedPrice\PromocodeRule $model
 * @var DotPlant\Store\models\extendedPrice\ExtendedPriceRule $rule
 * @var array $promocodes
 */

use kartik\select2\Select2;

echo $form->field($rule, '[' . $rule->id . ']params[promocodes]')->widget(
    Select2::class,
    [
        'options' => ['multiple' => true, 'placeholder' => 'Select states ...'],
        'data' => $promocodes,
    ]
);
