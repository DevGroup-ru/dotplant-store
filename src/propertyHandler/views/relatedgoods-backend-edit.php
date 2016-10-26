<?php
/**
 * @var \yii\db\ActiveRecord $model
 * @var ActiveForm $form
 * @var \DevGroup\DataStructure\models\Property $property
 * @var yii\web\View $this
 * @var array $data
 */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

$url = Url::to(['/structure/entity-manage/goods-autocomplete']);

echo Html::tag(
    'div',
    $form->field($model, $property->key)
        ->label($property->name)
        ->widget(
            Select2::class,
            [
                'data' => $data,
                'options' => [
                    'placeholder' => Yii::t('dotplant.store', 'Select a value ...'),
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'multiple' => (int)$property->allow_multiple_values === 1,
                    'ajax' => [
                        'url' => $url,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }'),
                        'delay' => '400',
                        'error' => new JsExpression('function(error) {alert(error.responseText);}'),
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(parent) { return parent.text; }'),
                    'templateSelection' => new JsExpression('function (parent) { return parent.text; }'),
                ]
            ]
        ),
    [
        'style' => 'overflow: auto;'
    ]
);
