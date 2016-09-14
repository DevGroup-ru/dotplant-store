<?php

namespace DotPlant\Store\actions\order;

use DotPlant\Store\components\Store;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;

/**
 * Class SingleStepOrderAction
 * @package DotPlant\Store\actions\order
 */
class SingleStepOrderAction extends Action
{
    /**
     * @var string the current action route
     */
    public $actionRoute = '/store/order/create';

    /**
     * @var array the route to payment action
     */
    public $paymentRoute = ['/store/order/payment'];

    /**
     * @var array the route to cart index action
     */
    public $cartRoute = ['/store/cart'];

    /**
     * @var string the action view file
     */
    public $viewFile = 'single-step-order';

    public function run($hash = null)
    {
        $order = !empty($hash) ? Store::getOrder($hash) : new Order;
        if ($order === null) {
            throw new BadRequestHttpException;
        }
        if ($order->isNewRecord) {
            $cart = Store::getCart(false);
            if ($cart === null || $cart->items_count == 0) {
                Yii::$app->session->setFlash('error', Yii::t('dotplant.store', 'Cart is empty'));
                return $this->controller->redirect($this->cartRoute);
            }
            if ($cart->is_locked == 1) {
                return $this->controller->redirect(
                    ArrayHelper::merge($this->actionRoute, ['hash' => $cart->items[0]->order->hash])
                );
            }
            $orderDeliveryInformation = new OrderDeliveryInformation;
            $orderDeliveryInformation->loadDefaultValues();
        } else {
            $orderDeliveryInformation = $order->deliveryInformation; // @todo: add a check
        }
        $order->scenario = 'single-step-order';
        $orderDeliveryInformation->context_id = Yii::$app->multilingual->context_id;
        if ($orderDeliveryInformation->load(Yii::$app->request->post()) // split it
            && $order->load(Yii::$app->request->post())
            && $orderDeliveryInformation->validate()
            && $order->validate()
        ) {
            if ($order->isNewRecord) {
                $order = Store::createOrder($cart);
                if ($order === null) {
                    throw new OrderException(Yii::t('dotplant.store', 'Something went wrong'));
                }
                $order->scenario = 'single-step-order';
            }
            $order->load(Yii::$app->request->post()); // @todo: refactor it
            $orderDeliveryInformation->order_id = $order->id;
            if ($order->save() && $orderDeliveryInformation->save()) {
                return $this->controller->redirect(
                    ArrayHelper::merge($this->paymentRoute, ['hash' => $order->hash])
                );
            }
            Yii::$app->session->setFlash('error', Yii::t('dotplant.store', 'Can not save delivery information'));
            return $this->controller->redirect(['one-step', 'hash' => $order->hash]);
        }
        return $this->controller->render(
            $this->viewFile,
            [
                'order' => $order,
                'orderDeliveryInformation' => $orderDeliveryInformation,
            ]
        );
    }
}
