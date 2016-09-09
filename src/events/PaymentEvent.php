<?php


namespace DotPlant\Store\events;


use yii\base\Event;

class PaymentEvent extends Event
{
    public $orderHash;
    public $logData;
    public $status;
}