<?php
/**
 * @var ExtendedPriceRule[] $acceptableRules
 * @var \yii\web\View $this
 */

use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<?php $this->beginBlock('extendedPriceWidget'); ?>
    <a class="btn btn-primary" data-toggle="modal" data-target="#extendedPriceWidget">
        <?= Yii::t(
            'dotplant.store',
            'Extended price'
        ); ?>
    </a>
<?php $this->endBlock(); ?>
<?php
Modal::begin(
    [
        'header' => Html::tag('h2', Yii::t('dotplant.store', 'Extended price')),
        'id' => 'extendedPriceWidget'
    ]
);
foreach ($acceptableRules as $acceptableRule) {
    $model = $acceptableRule->extendedPrice;
    if (is_null($model)) {
        $model = new ExtendedPrice();
        $model->loadDefaultValues();
    }
    echo $this->render(
        '@DotPlant/Store/views/extended-price-manage/_form',
        [
            'model' => $model,
            'formAction' => Url::to(['/store/extended-price-manage/extended-price-entity']),
            'additionalFields' => ['extendedPriceRule' => $acceptableRule->toArray()],
        ]
    );
}


Modal::end();
$this->registerJs('$(".tab-pane.active:first").append(\''. str_replace(["\r","\n"], '', $this->blocks['extendedPriceWidget']) .'\')');