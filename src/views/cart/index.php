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
        'success': function (data) {console.log(data);},
        'error': function (error) {console.log(error);}
    });
    return false;
});
JS;
$this->registerJs($js);

?>
<h1><?= $this->title ?></h1>

<form action="<?= \yii\helpers\Url::toRoute(['/store/cart/add']) ?>" data-action="ajax-tester">
    <input type="text" name="goodsId" />
    <input type="submit" value="Add" />
</form>

<table class="table table-striped table-condensed table-bordered">
    <?php foreach ($model->items as $item): ?>
        <tr>
            <td><?= $item->goods_id ?></td>
            <td>
                <form action="<?= \yii\helpers\Url::toRoute(['/store/cart/change-quantity']) ?>" data-action="ajax-tester">
                    <input type="hidden" name="id" value="<?= $item->id ?>" />
                    <input type="number" name="quantity" value="<?= $item->quantity ?>" />
                    <input type="submit" value="Save" />
                </form>
            </td>
            <td><?= $item->total_price_with_discount ?></td>
            <td>
                <form action="<?= \yii\helpers\Url::toRoute(['/store/cart/remove']) ?>" data-action="ajax-tester">
                    <input type="hidden" name="id" value="<?= $item->id ?>" />
                    <input type="submit" value="Remove" />
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="<?= \yii\helpers\Url::toRoute(['/store/order/create']) ?>" class="btn btn-primary">Create order</a>
