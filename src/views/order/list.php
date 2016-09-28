<?php
/**
 * @var $this yii\web\View
 * @var $orders DotPlant\Store\models\order\Order[]
 */

use yii\helpers\Html;

$this->title = Yii::t('dotplant.store', 'Orders list');
$this->params['breadcrumbs'] = [$this->title];
?>
<div class="box">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-12">
                <?php if (empty($orders) === true): ?>
                    <p><?= Yii::t('dotplant.store', 'No orders') ?></p>
                <?php else: ?>
                    <table class="table table-bordered table-condensed table-striped">
                        <tr>
                            <td><?= Yii::t('dotplant.store', 'Status') ?></td>
                            <td><?= Yii::t('dotplant.store', 'Items count') ?></td>
                            <td><?= Yii::t('dotplant.store', 'Total price with discount') ?></td>
                            <td><?= Yii::t('dotplant.store', 'Total price without discount') ?></td>
                            <td><?= Yii::t('dotplant.store', 'Manager') ?></td>
                            <td><?= Yii::t('dotplant.store', 'Updated at') ?></td>
                            <td>
                            </td>
                        </tr>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order->status_id ?></td>
                                <td><?= $order->items_count ?></td>
                                <td><?= $order->total_price_with_discount ?></td>
                                <td><?= $order->total_price_without_discount ?></td>
                                <td><?= $order->manager_id ?></td>
                                <td><?= $order->updated_at ?></td>
                                <td>
                                    <div class="btn-group">
                                        <?= Html::a(
                                            '<i class="fa fa-eye"></i>',
                                            [
                                                '/store/order/show',
                                                'hash' => $order->hash,
                                                ['class' => 'btn-info btn btn-sm'],
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
