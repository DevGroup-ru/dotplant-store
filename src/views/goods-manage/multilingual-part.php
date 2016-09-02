<?php

/**
 * @var yii\web\View $this
 * @var \DotPlant\Store\models\goods\Goods $model
 * @var yii\widgets\ActiveForm $form
 * @var integer $language_id
 * @var \DevGroup\Multilingual\models\Language $language
 * @var string $attributePrefix
 */

use kartik\switchinput\SwitchInput;

?>
<?= $form->field($model, $attributePrefix . 'is_active')->widget(SwitchInput::class) ?>
<?= $form->field($model, $attributePrefix . 'name') ?>
<?= $form->field($model, $attributePrefix . 'title') ?>
<?= $form->field($model, $attributePrefix . 'h1') ?>
<?= $form->field($model, $attributePrefix . 'slug') ?>
<?= $form->field($model, $attributePrefix . 'announce') ?>
<?= $form->field($model, $attributePrefix . 'content') ?>
<?= $form->field($model, $attributePrefix . 'meta_description') ?>

