<?php


namespace DotPlant\Store\actions\order;


use DotPlant\Store\components\Store;
use Yii;
use yii\base\Action;
use yii\web\BadRequestHttpException;

class PaymentAction extends Action
{
    protected $_order;

    public function beforeRun()
    {
        $hash = Yii::$app->request->get('hash');
        $this->_order = Store::getOrder($hash);
        if ($this->_order === null) {
            throw new BadRequestHttpException;
        }
        return parent::beforeRun();
    }
}