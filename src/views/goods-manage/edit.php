<?php
/**
 * @var \DotPlant\Store\models\goods\Goods $goods
 * @var $this \yii\web\View
 * @var bool $canSave
 * @var bool $undefinedType
 * @var [] $startCategory
 */

use DevGroup\AdminUtils\FrontendHelper;
use dmstr\widgets\Alert;
use DevGroup\DataStructure\widgets\PropertiesForm;
use DevGroup\Multilingual\widgets\MultilingualFormTabs;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\select2\Select2;
use devgroup\JsTreeWidget\widgets\TreeWidget;
use DotPlant\Store\assets\StoreAsset;
use DotPlant\Store\models\goods\GoodsCategory;
use DotPlant\EntityStructure\models\Entity;
use yii\web\View;
use DotPlant\Store\models\vendor\Vendor;
use yii\bootstrap\ActiveForm;
use DotPlant\Store\models\goods\CategoryGoods;

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
$this->params['breadcrumbs'][] = $this->title;
$url = Url::to(['/structure/entity-manage/goods-autocomplete']);
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
$form = ActiveForm::begin(
    [
        'id' => 'page-form',
        //    'options' => [
        //        'enctype' => 'multipart/form-data'
        //    ]
    ]
);
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
                <div class="row">
                    <div class="col-sm-12 col-md-6">
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
                    <div class="col-sm-6">
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
            <?php if (true === $canSave) : ?>
                <div class="btn-group pull-right" role="group" aria-label="Edit buttons">
                    <?= FrontendHelper::formSaveButtons($goods); ?>
                </div>
                <div class="clearfix"></div>
            <?php endif; ?>
        </div>
    </div>
<?php $form::end(); ?>