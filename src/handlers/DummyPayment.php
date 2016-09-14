<?php


namespace DotPlant\Store\handlers;

use DotPlant\Store\components\Store;
use DotPlant\Store\events\PaymentEvent;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\price\DummyTax;
use DotPlant\Store\Module;
use Yii;

class DummyPayment extends AbstractPaymentType
{
    public function init()
    {
        $this->on(Module::EVENT_PAYMENT_STATUS_FORMED, [self::className(), 'onPaymentFormed']);
        parent::init();
    }

    /**
     * @param Order $order
     * @param string $currencyIsoCode
     * @param OrderDeliveryInformation $shipping
     * @param DummyTax $tax
     */
    public function pay($order, $currencyIsoCode, $shipping, $tax)
    {
        $event = new PaymentEvent();
        $event->order_id = $order->id;
        $event->payment_id = $this->_paymentId;
        $event->start_time = time();
        $event->end_time = time();
        $event->sum = $order->total_price_with_discount;
        $event->currency_iso_code = $currencyIsoCode;
        $event->payment_data = [];
        $event->payment_result = ['status' => Module::EVENT_PAYMENT_STATUS_FORMED];
        $this->trigger(Module::EVENT_PAYMENT_STATUS_FORMED, $event);
    }

    public function refund($order, $currency, $amount)
    {
        // TODO: Implement refund() method.
    }

    public function checkResult($order)
    {
        return true;
    }

    public static function onPaymentFormed(PaymentEvent $event)
    {
        $order = Order::findOne($event->order_id);
        Store::markOrderAsPaid($order);
        return Yii::$app->controller->redirect(['payment/success', 'hash' => $order->hash]);
    }
}
