<?php


namespace DotPlant\Store\actions\order;


use DotPlant\Store\components\Store;

class PaymentSuccessAction extends PaymentAction
{
    public function run($hash)
    {
        if (Store::checkOrderIsPaid($this->_order) === false) {
            return $this->controller->redirect(['payment']);
        }
        return $this->controller->render('success');
    }
}