<?php

namespace DotPlant\Store\controllers;

use DevGroup\Frontend\controllers\FrontendController;
use DevGroup\Frontend\Universal\SuperAction;
use DotPlant\Monster\models\ServiceEntity;
use DotPlant\Monster\Universal\ServiceMonsterAction;
use DotPlant\Store\actions\HashCheckAction;
use DotPlant\Store\actions\order\PaymentCheckAction;
use DotPlant\Store\actions\order\PaymentPayAction;
use DotPlant\Store\actions\order\PaymentSuccessAction;
use DotPlant\Store\actions\order\SingleStepOrderAction;
use DotPlant\Store\components\OrderByHashProvider;
use DotPlant\Store\components\OrderDeliveryInformationByHashProvider;
use DotPlant\Store\components\OrderSingleStepProvider;
use DotPlant\Store\components\PaymentByHashProvider;
use DotPlant\Store\components\UserOrdersProvider;
use DotPlant\Store\components\Store;
use yii\base\Exception;

class OrderController extends FrontendController
{
    public function actions()
    {
        return [
            'error' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'order',
                    ],
                ],
            ],
            'list' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'order',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = UserOrdersProvider::class;
                        },
                    ],
                ],
            ],
            'show' => [
                'class' => SuperAction::class,
                'actions' => [
                    ['class' => HashCheckAction::class],
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'order',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = OrderByHashProvider::class;
                            $entity->providers[] = OrderDeliveryInformationByHashProvider::class;
                            $entity->providers[] = PaymentByHashProvider::class;
                        },
                    ],
                ],
            ],
            'refund' => [
                'class' => SuperAction::class,
                'actions' => [
                    ['class' => HashCheckAction::class],
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'order',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = OrderByHashProvider::class;
                            $entity->providers[] = PaymentByHashProvider::class;
                        },
                    ],
                ],
            ],
            'create' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'order',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = OrderSingleStepProvider::class;
                        },
                    ],
                ],
            ],
            'payment' => PaymentPayAction::class,
            'check' => PaymentCheckAction::class,
            'success' => PaymentSuccessAction::class,
            'old-create' => [
                'class' => SingleStepOrderAction::class,
            ],

        ];
    }

    public function actionCancel($hash)
    {
        $order = Store::getOrder($hash);
        if (Store::checkOrderIsPaid($order)) {
            throw new Exception('Canceling paid order is not implemented');
        } else {
            $order->status_id = Store::getCanceledOrderStatusId($order->context_id);
            \Yii::$app->session->setFlash('success', \Yii::t('dotplant.store', 'Order successfully canceled'));
        }
    }

}
