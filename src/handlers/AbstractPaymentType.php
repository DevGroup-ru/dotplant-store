<?php


namespace DotPlant\Store\handlers;


use DotPlant\Store\events\PaymentEvent;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\payment\PaymentTransaction;

abstract class AbstractPaymentType extends \yii\base\Component
{
    /**
     * @param Order $order
     * @param $currency
     * @param $shipping
     * @param $tax
     *
     * @return mixed
     */
    abstract public function pay($order, $currency, $shipping, $tax);

    abstract public function refund($order, $currency, $amount);

    abstract public function checkResult($order);

    public function trigger($name, PaymentEvent $event)
    {
        $transaction = new PaymentTransaction();
        $transaction->logDataFromEvent($event);
        $transaction->save();
        parent::trigger($name, $event);
    }

    // @todo implement
    // public static function getRules();
    // public static function getLabels();
}