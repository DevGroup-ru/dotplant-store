<?php

/**
 * @var \DotPlant\Store\models\order\Cart $model
 * @var yii\web\View $this
 */

$this->title = Yii::t('dotplant.store', 'Cart');
$js = <<<JS
jQuery('form[data-action="ajax-tester"]').submit(function (event) {
    var form = jQuery(this);
    jQuery.ajax({
        'type': 'post',
        'url': form.attr('action'),
        'data': form.serialize(),
        'dataType': 'json',
        'success': function (data) {console.log(data);location.reload();},
        'error': function (error) {console.log(error);location.reload();}
    });
    return false;
});
JS;
$this->registerJs($js);

?>
<h1><?= $this->title ?></h1>

<form action="<?= \yii\helpers\Url::toRoute(['/store/cart/add']) ?>" data-action="ajax-tester">
    <input type="text" name="goodsId"/>
    <input type="submit" value="Add"/>
</form>
<?php if ($model !== null && $model->items_count > 0) : ?>
    <?=
    \DotPlant\Store\widgets\common\OrderItems::widget(
        [
            'languageId' => Yii::$app->multilingual->language_id,
            'model' => $model,
            'viewFile' => 'cart-items',
        ]
    )
    ?>
    <?= Yii::t('dotplant.store', 'Delivery term is {term} days', ['term' => $model->getDeliveryTerm()]) ?>
    <div class="clearfix"></div>
    <?php if ($model->canEdit()) : ?>
        <a href="<?= \yii\helpers\Url::toRoute(['/store/order/create']) ?>" class="btn btn-primary">Create an order</a>
    <?php else : ?>
        <a href="<?= \yii\helpers\Url::toRoute(['/store/order/create', 'hash' => $model->items[0]->order->hash]) ?>"
           class="btn btn-primary">Edit an order</a>
    <?php endif; ?>
<?php else : ?>
    <p><?= Yii::t('dotplant.store', 'Cart has no items') ?></p>
<?php endif; ?>

