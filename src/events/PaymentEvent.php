<?php


namespace DotPlant\Store\events;


use yii\base\Event;

class PaymentEvent extends Event
{
    public $order_id;
    public $payment_id;
    public $start_time;
    public $end_time;
    public $sum;
    public $currency_iso_code;
    public $payment_data;
    public $payment_result;
}