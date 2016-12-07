<?php

/**
 * @var \DotPlant\Store\models\goods\Goods $goods
 * @var $this \yii\web\View
 * @var bool $canSave
 * @var bool $undefinedType
 * @var null|array $startCategory
 * @var GoodsWarehouse[] $prices
 * @var bool $showChildren
 * @var \yii\data\ActiveDataProvider $optionsDataProvider
 */

use DevGroup\AdminUtils\events\ModelEditForm;
use DevGroup\AdminUtils\FrontendHelper;
use DevGroup\DataStructure\widgets\PropertiesForm;
use DevGroup\Multilingual\widgets\ContextTabsWidget;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use dmstr\widgets\Alert;
use DotPlant\Currencies\models\Currency;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\actions\goods\GoodsManageAction;
use DotPlant\Store\assets\StoreAsset;
use DotPlant\Store\helpers\GoodsEditTreeTabHelper;
use DotPlant\Store\models\goods\GoodsCategory;
use DotPlant\Store\models\vendor\Vendor;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use DotPlant\Store\Module;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

$goodsTypes = $goods->getTypes();
$goodsType = $goodsTypes[$goods->getType()];
$goodsRole = isset($goods->getRoles()[$goods->getType()]) ? $goods->getRoles()[$goods->getType()] : Yii::t(
    'dotplant.store',
    'Not set'
);
$this->title = empty($goods->id) ? Yii::t('dotplant.store', 'New {goods}', ['goods' => $goodsType]) : Yii::t(
    'dotplant.store',
    'Edit {goods} #{id}',
    ['goods' => $goodsType, 'id' => $goods->id]
);


$this->params['breadcrumbs'][] = [
    'url' => ['/structure/entity-manage/products'],
    'label' => Yii::t('dotplant.store', 'Goods management'),
];

if (empty($goods->mainCategory) === false) {
    $this->params['breadcrumbs'][] = [
        'url' => ['/structure/entity-manage/products', 'id' => $goods->mainCategory->id],
        'label' => Yii::t('dotplant.store', $goods->mainCategory->name),
    ];
}

$this->params['breadcrumbs'][] = $this->title;
$categoryEntityId = Entity::getEntityIdForClass(GoodsCategory::class);
$missingParamText = Yii::t('dotplant.store', 'Missing param');
$mainStructureId = Html::getInputId($goods, 'main_structure_id');
$formName = $goods->formName();
$js = <<<JS
    window.DotPlantStore = {
        categoryEntityId : $categoryEntityId,
        missingParamText : '$missingParamText',
        mainCategorySelector : '#$mainStructureId',
        goodsFormName : '$formName'
    };
JS;
$this->registerJs($js, View::POS_HEAD);
StoreAsset::register($this);

Module::module()->trigger(GoodsManageAction::EVENT_BEFORE_FORM);
$form = ActiveForm::begin(
    [
        'id' => 'page-form',
        //    'options' => [
        //        'enctype' => 'multipart/form-data'
        //    ]
    ]
);
$event = new ModelEditForm($form, $goods);
?>
<?= Alert::widget() ?>
<div class="nav-tabs-custom">
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#goods-data" data-toggle="tab" aria-expanded="true">
                <?= Yii::t('dotplant.store', 'Common') ?>
            </a>
        </li>
        <?php if (false === $goods->isNewRecord) : ?>
            <li class="">
                <a href="#goods-properties" data-toggle="tab" aria-expanded="false">
                    <?= Yii::t('dotplant.store', 'Goods properties') ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($showChildren) : ?>
            <li class="">
                <a href="#children" data-toggle="tab" aria-expanded="false">
                    <?= Yii::t('dotplant.store', 'Options / Children') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="goods-data">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <?= $form->errorSummary([$goods], ['class' => 'info-box bg-red well']) ?>
                    <?= $form->field($goods, 'vendor_id')->dropDownList(
                        Vendor::getArrayList(),
                        ['prompt' => Yii::t('dotplant.store', 'Choose vendor')]
                    ) ?>
                    <?= $form->field($goods, 'sku') ?>
                    <?php if (true === $undefinedType) : ?>
                        <?= $form->field($goods, 'type')->dropDownList($goodsTypes) ?>
                    <?php else : ?>
                        <?= $form->field($goods, 'type')->textInput(
                            ['value' => $goodsType, 'disabled' => 'disabled']
                        ) ?>
                    <?php endif; ?>
                    <?= $form->field($goods, 'role')->textInput(
                        [
                            'value' => $goodsRole,
                            'disabled' => 'disabled',
                        ]
                    ) ?>
                    <?= $form->field($goods, 'asin') ?>
                    <?= $form->field($goods, 'isbn') ?>
                    <?= $form->field($goods, 'upc') ?>
                    <?= $form->field($goods, 'ean') ?>
                    <?= $form->field($goods, 'jan') ?>
                </div>
                <div class="col-sm-12 col-md-6">
                    <?= ContextTabsWidget::widget(
                        [
                            'tabViewFile' => '@DotPlant/Store/views/goods-manage/context-tree-tab',
                            'handlers' => [new GoodsEditTreeTabHelper($goods, $startCategory, $form)],
                        ]
                    ) ?>

                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="row">
                <?php if (empty($prices) === false) : ?>
                    <div class="col-sm-12">
                        <table class="table ">
                            <caption><?= Yii::t('dotplant.store', 'Prices') ?></caption>
                            <?php $tableHead = true; ?>
                            <?php foreach ($prices as $key => $price) : ?>
                                <?php if ($tableHead === true) : ?>
                                    <tr>
                                        <td>
                                            <?= $price->warehouse->getAttributeLabel('name') ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('currency_iso_code'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('seller_price'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('retail_price'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('wholesale_price'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('available_count'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('is_unlimited'); ?>
                                        </td>
                                        <td>
                                            <?= $price->getAttributeLabel('is_allowed'); ?>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <?php $tableHead = false; ?>
                                <?php endif; ?>
                                <tr>
                                    <td>
                                        <?= $price->warehouse->name ?>
                                    </td>
                                    <td>
                                        <?= $form->field($price, "[$key]currency_iso_code")->label(false)
                                            ->dropDownList(
                                                ArrayHelper::map(
                                                    Currency::findAll(),
                                                    'iso_code',
                                                    'iso_code'
                                                )
                                            ); ?>
                                    </td>
                                    <td>
                                        <?= $form->field($price, "[$key]seller_price")
                                            ->input('number', ['step' => '0.01'])
                                            ->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $form->field($price, "[$key]retail_price")
                                            ->input('number', ['step' => '0.01'])
                                            ->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $form->field($price, "[$key]wholesale_price")
                                            ->input('number', ['step' => '0.01'])
                                            ->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $form->field($price, "[$key]available_count")
                                            ->input('number', ['step' => '1'])
                                            ->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $form->field(
                                            $price,
                                            "[$key]is_unlimited"
                                        )->widget(SwitchInput::class)->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $form->field(
                                            $price,
                                            "[$key]is_allowed"
                                        )->widget(SwitchInput::class)->label(false); ?>
                                    </td>
                                    <td>
                                        <?= $price->isNewRecord ?
                                            Yii::t('dotplant.store', 'New') :
                                            Yii::t('dotplant.store', 'Update'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                <?php endif; ?>
                <div class="clearfix"></div>


                <div class="col-sm-12">
                    <?= MultilingualFormTabs::widget(
                        [
                            'model' => $goods,
                            'childView' => '@DotPlant/Store/views/goods-manage/multilingual-part.php',
                            'form' => $form,
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="goods-properties">
            <?= PropertiesForm::widget(
                    [
                        'model' => $goods,
                        'form' => $form,
                    ]
                ) ?>
        </div>
        <?php if ($showChildren) : ?>
            <div class="tab-pane" id="children">
                <?=
                $this->render(
                    'tabChildren',
                    [
                        'childrenDataProvider' => $childrenDataProvider,
                        'goods' => $goods,
                        'child' => $child,
                        'allowedTypes' => $allowedTypes,
                    ]
                )
                ?>
            </div>
        <?php endif; ?>
        <?php Module::module()->trigger(GoodsManageAction::EVENT_FORM_BEFORE_SUBMIT, $event); ?>
        <?php if (true === $canSave) : ?>
            <div class="btn-group pull-right" role="group" aria-label="Edit buttons">
                <?= FrontendHelper::formSaveButtons($goods); ?>
            </div>
            <div class="clearfix"></div>
        <?php endif; ?>
        <?php Module::module()->trigger(GoodsManageAction::EVENT_FORM_AFTER_SUBMIT, $event); ?>
    </div>
</div>
<?php $form::end(); ?>
<?php Module::module()->trigger(GoodsManageAction::EVENT_AFTER_FORM, $event); ?>
