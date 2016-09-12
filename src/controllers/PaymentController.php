<?php


namespace DotPlant\Store\controllers;


use DotPlant\Store\components\payment\Payment;
use DotPlant\Store\components\Store;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\OrderDeliveryInformation;
use DotPlant\Store\models\price\DummyTax;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;

class PaymentController extends Controller
{
    /**
     * @var Order
     */
    private $_order;
    /**
     * @inheritdoc
     */
    public $defaultAction = 'pay';

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $hash = Yii::$app->request->get('hash');
        $this->_order = !empty($hash) ? Store::getOrder($hash) : null;
        if ($this->_order === null) {
            throw new BadRequestHttpException;
        }
        return parent::beforeAction($action);
    }

    /**
     * @param null $hash
     * @param null $paymentId
     *
     * @return string
     */
    public function actionPay($hash = null, $paymentId = null)
    {
        $shippingObject = OrderDeliveryInformation::findOne(['order_id' => $this->_order->id]);
        $taxNullObject = new DummyTax;
        Payment::pay($paymentId, $this->_order, $this->_order->currency_iso_code, $shippingObject, $taxNullObject);
        return $this->render('pay');
    }

    /**
     * @param null $hash
     *
     * @return string
     */
    public function actionCheck($hash = null)
    {
        return $this->render('check');
    }

    /**
     * @param null $hash
     *
     * @return string
     */
    public function actionSuccess($hash = null)
    {
        return $this->render('success');
    }

    /**
     * @param null $hash
     *
     * @return string
     */
    public function actionError($hash = null)
    {
        return $this->render('error');
    }

    /**
     * @param null $hash
     *
     * @return string
     */
    public function actionRefund($hash = null)
    {
        return $this->render('refund');
    }
}