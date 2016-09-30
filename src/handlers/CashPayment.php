<?php


namespace DotPlant\Store\handlers;

use DotPlant\Store\components\Store;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\price\DummyTax;
use DotPlant\Store\Module;
use Yii;

class CashPayment extends AbstractPaymentType
{
    /**
     * @param Order $order
     * @param string $currencyIsoCode
     * @param OrderDeliveryInformation $shipping
     * @param DummyTax $tax
     */
    public function pay($order, $currencyIsoCode, $shipping, $tax)
    {
        $this->logData(
            $order->id,
            $this->_paymentId,
            time(),
            time(),
            $order->total_price_with_discount,
            $currencyIsoCode,
            [],
            ['status' => Module::EVENT_PAYMENT_STATUS_FORMED]
        );
        $result = Store::markOrderAsPaid($order);
        if ($result) {
            $this->logData(
                $order->id,
                $this->_paymentId,
                time(),
                time(),
                $order->total_price_with_discount,
                $currencyIsoCode,
                [],
                ['status' => Store::getPaidOrderStatusId($order->context_id)]
            );
            Yii::$app->controller->redirect(['/store/order/show', 'hash' => $order->hash]);
        }
    }

    public function refund($order, $currency, $amount)
    {
        // TODO: Implement refund() method.
    }

    public function checkResult($order)
    {
        return true;
    }
}
