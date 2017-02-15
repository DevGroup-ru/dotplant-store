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
<?= $form->field($model, $attributePrefix . 'is_active')->widget(SwitchInput::class)->label( Yii::t('dotplant.store', 'Is Active')) ?>
<?= $form->field($model, $attributePrefix . 'name')->label( Yii::t('dotplant.store', 'Name')) ?>
<?= $form->field($model, $attributePrefix . 'title')->label( Yii::t('dotplant.store', 'Title')) ?>
<?= $form->field($model, $attributePrefix . 'h1')->label( Yii::t('dotplant.store', 'H1')) ?>
<?= $form->field($model, $attributePrefix . 'breadcrumbs_label')->label( Yii::t('dotplant.store', 'Breadcrumbs Label')) ?>
<?= $form->field($model, $attributePrefix . 'slug')->label( Yii::t('dotplant.store', 'Slug')) ?>
<?= $form->field($model, $attributePrefix . 'meta_description')->label( Yii::t('dotplant.store', 'Meta Description')) ?>
<?= $form->field($model, $attributePrefix . 'announce')->textarea(['rows' => 5])->label( Yii::t('dotplant.store', 'Announce')) ?>
<?= $form->field($model, $attributePrefix . 'description')->textarea(['rows' => 5])->label( Yii::t('dotplant.store', 'Description'));
