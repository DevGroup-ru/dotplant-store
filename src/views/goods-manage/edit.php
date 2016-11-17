<?php

/**
 * @var \DotPlant\Store\models\goods\Goods $goods
 * @var $this \yii\web\View
 * @var bool $canSave
 * @var bool $undefinedType
 * @var [] $startCategory
 * @var GoodsWarehouse[] $prices
 * @var bool $showOptions
 * @var \yii\data\ActiveDataProvider $optionsDataProvider
 */

use DevGroup\AdminUtils\events\ModelEditForm;
use DevGroup\AdminUtils\FrontendHelper;
use DevGroup\DataStructure\widgets\PropertiesForm;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use dmstr\widgets\Alert;
use DotPlant\Currencies\models\Currency;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\actions\goods\GoodsManageAction;
use DotPlant\Store\assets\StoreAsset;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\Store\models\goods\GoodsCategory;
use DotPlant\Store\models\vendor\Vendor;
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use DotPlant\Store\Module;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
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
$url = Url::to(['/structure/entity-manage/goods-autocomplete', 'product_id' => $goods->id]);
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
$checked = empty($startCategory) ? [] : is_array($startCategory) ? $startCategory : [$startCategory];
$checked = array_merge($checked, CategoryGoods::getBindings($goods->id));
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
        <?php if ($showOptions) : ?>
            <li class="">
                <a href="#options" data-toggle="tab" aria-expanded="false">
                    <?= Yii::t('dotplant.store', 'Options') ?>
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
                    <?= TreeWidget::widget(
                        [
                            'id' => 'goodsTreeWidget',
                            'treeDataRoute' => [
                                '/structure/entity-manage/category-tree',
                                'checked' => implode(',', $checked),
                            ],
                            'treeType' => TreeWidget::TREE_TYPE_ADJACENCY,
                            'plugins' => ['checkbox', 'types'],
                            'multiSelect' => true,
                            'contextMenuItems' => [],
                            'options' => [
                                'checkbox' => [
                                    'three_state' => false,
                                ],
                            ],
                        ]
                    ) ?>
                    <?= $form->field($goods, 'main_structure_id')->dropDownList(
                        ArrayHelper::map($goods->categories, 'id', 'name')
                    ) ?>


                    <div class="clearfix"></div>
                    <?php if ($goods->getHasChild() === true) : ?>
                        <label><?= Yii::t('dotplant.store', 'Child'); ?></label>

                        <?= Select2::widget([
                            'name' => 'childGoods',
                            'data' => $child,
                            'value' => array_keys($child),
                            'options' => [
                                'placeholder' => Yii::t('dotplant.store', 'Search for a child ...'),
                                'multiple' => true
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
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
                        ]);
                        ?>
                    <?php endif; ?>


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
        <?php if ($showOptions) : ?>
            <div class="tab-pane" id="options">
                <?=
                \yii\grid\GridView::widget(
                    [
                        'dataProvider' => $optionsDataProvider,
                        'columns' => [
                            [
                                'attribute' => 'name',
                                'options' => [
                                    'width' => '80%',
                                ],
                            ],
                            'is_active:boolean',
                            [
                                'attribute' => 'is_deleted',
                                'label' => Yii::t('dotplant.store', 'Show deleted?'),
                                'value' => function ($model) {
                                    return $model->isDeleted() === true ? Yii::t(
                                        'dotplant.store',
                                        'Deleted'
                                    ) : Yii::t('dotplant.store', 'Active');
                                },
                                'filter' => [
                                    Yii::t('dotplant.store', 'Show only active'),
                                    Yii::t('dotplant.store', 'Show only deleted')
                                ],
                                'filterInputOptions' => [
                                    'class' => 'form-control',
                                    'id' => null,
                                    'prompt' => Yii::t('dotplant.store', 'Show all')
                                ]
                            ],
                            [
                                'class' => \DevGroup\AdminUtils\columns\ActionColumn::class,
                                'buttons' => function ($model, $key, $index, $column) {

                                    $result = [
                                        'edit' => [
                                            'url' => '/structure/entity-manage/goods-manage',
                                            'icon' => 'pencil',
                                            'class' => 'btn-info',
                                            'label' => Yii::t('dotplant.store', 'Edit'),
                                            'keyParam' => 'product_id',
                                        ]
                                    ];

                                    if ($model->isDeleted() === false) {
                                        $result['soft-delete'] = [
                                            'url' => '/structure/entity-manage/goods-delete',
                                            'icon' => 'trash-o',
                                            'class' => 'btn-danger',
                                            'label' => Yii::t('dotplant.store', 'Delete'),
                                            'keyParam' => 'product_id',
                                        ];
                                    } else {
                                        $result['restore'] = [
                                            'url' => '/structure/entity-manage/goods-restore',
                                            'icon' => 'undo',
                                            'class' => 'btn-info',
                                            'label' => Yii::t('dotplant.store', 'Restore'),
                                            'keyParam' => 'product_id',
                                        ];
                                    }


                                    return $result;
                                }
                            ]
                        ],
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
