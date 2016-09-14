<?php
/**
 * @var \DotPlant\Store\models\goods\Goods $goods
 * @var $this \yii\web\View
 * @var bool $canSave
 */

use kartik\switchinput\SwitchInput;
use dmstr\widgets\Alert;
use DevGroup\DataStructure\widgets\PropertiesForm;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use yii\helpers\Html;

$goodsType = $goods->getTypes()[$goods->getType()];
$goodsRole = isset($goods->getRoles()[$goods->getType()])
    ? $goods->getRoles()[$goods->getType()]
    : Yii::t('dotplant.store', 'Not set');
$this->title = empty($goods->id)
    ? Yii::t('dotplant.store', 'New {goods}', ['goods' => $goodsType])
    : Yii::t('dotplant.store', 'Edit {goods} #{id}', ['goods' => $goodsType, 'id' => $goods->id]);

$this->params['breadcrumbs'][] = [
    'url' => ['/store/goods-manage/index'],
    'label' => Yii::t('dotplant.store', 'Goods management')
];
$this->params['breadcrumbs'][] = $this->title;

$form = \yii\bootstrap\ActiveForm::begin([
    'id' => 'page-form',
//    'options' => [
//        'enctype' => 'multipart/form-data'
//    ]
]);
?>
<?= Alert::widget() ?>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#goods-data" data-toggle="tab" aria-expanded="true">
                    <?= Yii::t('dotplant.store', 'Main options') ?>
                </a>
            </li>
            <?php if (false === $goods->isNewRecord) : ?>
                <li class="">
                    <a href="#goods-properties" data-toggle="tab" aria-expanded="false">
                        <?= Yii::t('dotplant.store', 'Goods properties') ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="goods-data">
                <div class="col-sm-12 col-md-6">
                    <?= $form->field($goods, 'parent_id') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <div class="row">
                        <div class="col-sm-4">
                            <?= $form->field($goods, 'vendor_id') ?>
                            <?= $form->field($goods, 'main_structure_id') ?>
                            <?= $form->field($goods, 'sku') ?>
                            <?= $form->field($goods, 'type')
                                ->textInput([
                                    'value' => $goodsType,
                                    'disabled' => 'disabled'
                                ]) ?>
                            <?= $form->field($goods, 'role')
                                ->textInput([
                                    'value' => $goodsRole,
                                    'disabled' => 'disabled'
                                ]) ?>
                        </div>
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-4">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <?= MultilingualFormTabs::widget([
                            'model' => $goods,
                            'childView' => '@DotPlant/Store/views/goods-manage/multilingual-part.php',
                            'form' => $form,
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="goods-properties">
                <?= PropertiesForm::widget([
                    'model' => $goods,
                    'form' => $form,
                ]) ?>
            </div>
            <?php if (true === $canSave) : ?>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="btn-group pull-right" role="group" aria-label="Edit buttons">
                            <button type="submit" class="btn btn-success pull-right">
                                <?= Yii::t('dotplant.store', 'Save') ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $form::end(); ?>