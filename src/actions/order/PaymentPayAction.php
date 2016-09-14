<?php


namespace DotPlant\Store\actions\order;


use DotPlant\Store\components\payment\Payment;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\price\DummyTax;

class PaymentPayAction extends PaymentAction
{
    public function run($hash, $paymentId)
    {
        $shippingObject = OrderDeliveryInformation::findOne(['order_id' => $this->_order->id]);
        $this->_order->payment_id = $paymentId;
        $taxNullObject = new DummyTax;
        Payment::pay($paymentId, $this->_order, $this->_order->currency_iso_code, $shippingObject, $taxNullObject);
        return $this->controller->render('pay');
    }
}