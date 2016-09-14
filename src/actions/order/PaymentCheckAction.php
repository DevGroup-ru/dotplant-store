<?php


namespace DotPlant\Store\actions\order;


use DotPlant\Store\components\payment\Payment;
use Yii;

class PaymentCheckAction extends PaymentAction
{
    public function run($hash, $paymentId)
    {
        $paymentResult = Payment::checkResult($paymentId, $this->_order);
        if ($paymentResult) {
            return Yii::$app->runAction('success', ['hash' => $hash]);
        }
        return Yii::$app->runAction('error', ['hash' => $hash]);
    }
}