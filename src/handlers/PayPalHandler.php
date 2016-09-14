<?php


namespace DotPlant\Store\handlers;

use DotPlant\Currencies\models\Currency;
use DotPlant\Store\events\PaymentEvent;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\OrderItem;
use DotPlant\Store\Module;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use yii\helpers\Url;

class PayPalHandler extends AbstractPaymentType
{
    const MOD_SANDBOX = 'sandbox';
    const MOD_PRODUCTION = 'live';

    private $_apiContext;
    public $clientId;
    public $clientSecret;
    public $sandbox;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_apiContext = new ApiContext(new OAuthTokenCredential($this->clientId, $this->clientSecret));
        $this->_apiContext->setConfig(
            [
                'mode' => true === $this->sandbox ? static::MOD_SANDBOX : static::MOD_PRODUCTION,
                'log.LogEnabled' => false,
                'cache.enabled' => true,
                'cache.FileName' => \Yii::getAlias('@runtime/paypal.cache'),
            ]
        );
    }

    /**
     * PayPal API integration; do not use
     *
     * @param \DotPlant\Store\models\order\Order $order
     * @param string $currencyIsoCode
     * @param Delivery $shipping
     * @param $tax
     */
    public function pay($order, $currencyIsoCode, $shipping, $tax)
    {
        $payer = (new Payer())->setPaymentMethod('paypal');
        $priceSubTotal = 0;
        /** @var ItemList $itemList */
        $itemList = array_reduce(
            $order->items,
            function ($result, $item) use (&$priceSubTotal, $currencyIsoCode) {
                /** @var OrderItem $item */
                /** @var Goods $good */
                $good = $item->good;
                $price = $good->getPrice();
                $priceSubTotal = $priceSubTotal + $item->total_price_with_discount;
                /** @var ItemList $result */
                return $result->addItem(
                    (new Item())->setName($good->name)->setCurrency($currencyIsoCode)->setPrice($price)->setQuantity(
                        $item->quantity
                    )
                );
            },
            new ItemList()
        );
        $priceTotal = $order->total_price_with_discount;


        $details = (new Details())->setShipping($shipping->price)->setSubtotal($priceSubTotal)->setTax($tax->getTax());
        $amount = (new Amount())->setCurrency($currencyIsoCode)->setTotal($priceTotal)->setDetails($details);
        $transaction = (new Transaction())->setAmount($amount)->setItemList($itemList)->setDescription(
            "Order " . $order->hash
        )->setInvoiceNumber($order->hash);
        $urls = (new RedirectUrls())->setReturnUrl(Url::to(['payment']))->setCancelUrl($canselUrl);
        $payment = (new Payment())->setIntent('sale')->setPayer($payer)->setTransactions(
            [$transaction]
        )->setRedirectUrls($urls);
        $link = null;

        $startTime = time();
        $paymentSerialized = serialize($payment);

        $this->logData(
            $order->id,
            $this->_paymentId,
            $startTime,
            time(),
            $priceTotal,
            $currencyIsoCode,
            $paymentSerialized,
            ['status' => Module::EVENT_PAYMENT_STATUS_FORMED]
        );

        try {
            $formedPayment = $payment->create($this->_apiContext);
            $link = $formedPayment->getApprovalLink();

            $this->logData(
                $order->id,
                $this->_paymentId,
                $startTime,
                time(),
                $priceTotal,
                $currencyIsoCode,
                $paymentSerialized,
                [
                    'status' => Module::EVENT_PAYMENT_STATUS_PROCESSED,
                    'paymentObject' => serialize($formedPayment),
                ]
            );

        } catch (\Exception $e) {
            $link = null;

            $this->logData(
                $order->id,
                $this->_paymentId,
                $startTime,
                time(),
                $priceTotal,
                $currencyIsoCode,
                $paymentSerialized,
                [
                    'status' => Module::EVENT_PAYMENT_STATUS_ERROR,
                    'paymentObject' => serialize($payment),
                ]
            );

        }
        return $this->render(
            'paypal',
            [
                'order' => $order,
                'transaction' => $this->transaction,
                'approvalLink' => $link,
            ]
        );
    }

    public function refund($order, $currency, $amount)
    {
        // TODO: Implement refund() method.
    }

    public function checkResult($order)
    {
        // TODO: Implement checkResult() method.
    }
}
