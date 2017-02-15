<?php
use devgroup\JsTreeWidget\widgets\TreeWidget;
use DotPlant\Store\models\goods\GoodsCategory;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var \yii\web\View $this */
/** @var array $data */
/** @var int $context_id */
/**  @codeCoverageIgnore */
$contextIdVal = intval($context_id);
$mainStructureId = Html::getInputId($data['tree']['goods'], 'mainStructures[' . $contextIdVal . ']');
echo TreeWidget::widget(
    [
        'id' => 'goodsTreeWidget' . intval($context_id),
        'treeDataRoute' => [
            '/structure/entity-manage/category-tree',
            'checked' => implode(',', $data['tree']['checked']),
            'contextId' => $context_id,
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
);

$js = <<<JS
    window.DotPlantStore.mainCategorySelector[$contextIdVal] = '#$mainStructureId';
JS;
$this->registerJs($js, View::POS_HEAD);
$categoriesByContextId = array_filter(
    $data['tree']['goods']->categories,
    function (GoodsCategory $item) use ($context_id) {
        return $context_id === $item->context_id;
    }
);

echo $data['tree']['form']->field(
    $data['tree']['goods'],
    'mainStructures[' . $contextIdVal . ']'
)->dropDownList(
    ArrayHelper::map($categoriesByContextId, 'id', 'name')
)->label(Yii::t('dotplant.store', 'Main Structures'));
