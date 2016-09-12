<?php


namespace DotPlant\Store\handlers;


use DotPlant\Currencies\models\Currency;
use DotPlant\Store\events\PaymentEvent;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\OrderItem;
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
     *
     * @return mixed
     *
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
                    )->setUrl($goodUrl)
                );
            },
            new ItemList()
        );
        $priceTotal = $order->total_price_with_discount;


        $details = (new Details())->setShipping($shipping->price)->setSubtotal($priceSubTotal)->setTax($tax->getTax());
        $amount = (new Amount())->setCurrency($currencyIsoCode)->setTotal($priceTotal)->setDetails($details);
        $transaction = (new Transaction())->setAmount($amount)->setItemList($itemList)->setDescription(
            $description
        )->setInvoiceNumber($invoiceId);
        $urls = (new RedirectUrls())->setReturnUrl($returnUrl)->setCancelUrl($canselUrl);
        $payment = (new Payment())->setIntent('sale')->setPayer($payer)->setTransactions(
            [$transaction]
        )->setRedirectUrls($urls);
        $link = null;

        $event = new PaymentEvent();
        $event->order_id = $order->id;
        $event->payment_id = $this->_paymentId;
        $event->start_time = time();
        $event->end_time = time();
        $event->sum = $priceTotal;
        $event->currency_iso_code = $currencyIsoCode;
        $event->payment_data = ['paymentObject' => serialize($payment)];
        $event->payment_result = ['status' => 'formed'];

        $this->trigger('formed', $event);

        try {
            $formedPayment = $payment->create($this->_apiContext);
            $link = $formedPayment->getApprovalLink();
            $event->end_time = time();
            $event->payment_result = ['status' => 'processed', 'paymentObject' => serialize($formedPayment)];

            $this->trigger('processed', $event);
        } catch (\Exception $e) {
            $link = null;

            $event->end_time = time();
            $event->payment_result = ['status' => 'failed', 'paymentObject' => serialize($payment)];

            $this->trigger('fail', $event);
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