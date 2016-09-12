<?php


namespace DotPlant\Store\handlers;


use DotPlant\Store\events\PaymentEvent;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\order\OrderTransaction;
use DotPlant\Store\models\price\DummyTax;

abstract class AbstractPaymentType extends \yii\base\Component
{
    protected $_paymentId;

    /**
     * @param Order $order
     * @param string $currencyIsoCode
     * @param OrderDeliveryInformation $shipping
     * @param DummyTax $tax
     *
     * @return mixed
     */
    abstract public function pay($order, $currencyIsoCode, $shipping, $tax);

    abstract public function refund($order, $currency, $amount);

    abstract public function checkResult($order);

    public function setPaymentId($id)
    {
        $this->_paymentId = $id;
    }

    public function trigger($name, PaymentEvent $event)
    {
        $transaction = new OrderTransaction;
        $transaction->logDataFromEvent($event);
        $transaction->save();
        parent::trigger($name, $event);
    }

    // @todo implement
    // public static function getRules();
    // public static function getLabels();
}