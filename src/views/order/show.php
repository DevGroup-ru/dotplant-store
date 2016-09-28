<?php
/**
 * @var $this yii\web\View
 * @var $order DotPlant\Store\models\order\Order
 * @var $orderDeliveryInformation DotPlant\Store\models\order\OrderDeliveryInformation
 */


use DotPlant\Store\widgets\common\OrderItems;

$this->title = Yii::t('dotplant.store', 'Order');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('dotplant.store', 'Orders list'), 'url' => ['list']],
    $this->title,
];
?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <p><?= Yii::t('dotplant.store', 'Order items') ?></p>
                <?= OrderItems::widget(['model' => $order]) ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?php if (is_null($orderDeliveryInformation) === false): ?>
                    <p><?= Yii::t('dotplant.store', 'Order delivery information') ?></p>
                    <table class="table table-striped table-condensed table-bordered">
                        <tr>
                            <th><?= Yii::t('dotplant.store', 'Full name') ?></th>
                            <td><?= $orderDeliveryInformation->full_name ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('dotplant.store', 'E-mail') ?></th>
                            <td><?= $orderDeliveryInformation->email ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('dotplant.store', 'Phone number') ?></th>
                            <td><?= $orderDeliveryInformation->phone ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('dotplant.store', 'Zip code') ?></th>
                            <td><?= $orderDeliveryInformation->zip_code ?></td>
                        </tr>
                        <tr>
                            <th><?= Yii::t('dotplant.store', 'Address') ?></th>
                            <td><?= $orderDeliveryInformation->address ?></td>
                        </tr>
                    </table>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
