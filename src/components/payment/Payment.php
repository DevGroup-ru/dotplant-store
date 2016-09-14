<?php


namespace DotPlant\Store\components\payment;

use DotPlant\Store\handlers\AbstractPaymentType;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\price\DummyTax;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class Payment extends \yii\base\Component
{
    /**
     * @param $paymentId
     *
     * @return AbstractPaymentType
     */
    private static function createPayment($paymentId)
    {
        $paymentModel = \DotPlant\Store\models\order\Payment::findOne($paymentId);
        if (is_object($paymentModel) && $paymentModel->handler_class_name instanceof AbstractPaymentType) {
            /**
             * @var AbstractPaymentType $paymentObject
             */
            $paymentObject = Yii::createObject(
                ArrayHelper::merge(
                    ['class' => $paymentModel->handler_class_name],
                    $paymentModel->handler_params,
                    ['paymentId' => $paymentId]
                )
            );
            return $paymentObject;
        }
        throw new InvalidParamException(Yii::t('dotplant.store', 'Set correct payment ID'));
    }

    /**
     * @param int $paymentId
     * @param Order $order
     * @param string $currencyIsoCode
     * @param OrderDeliveryInformation $shipping
     * @param DummyTax $tax
     */
    public static function pay($paymentId, $order, $currencyIsoCode, $shipping, $tax)
    {
        $handler = static::createPayment($paymentId);
        $handler->pay($order, $currencyIsoCode, $shipping, $tax);
    }

    public static function refund($paymentId, $order, $currency, $amount)
    {
        $handler = static::createPayment($paymentId);
        $handler->refund($order, $currency, $amount);
    }

    public static function checkResult($paymentId, $order)
    {
        $handler = static::createPayment($paymentId);
        $handler->checkResult($order);
    }
}
