<?php

namespace DotPlant\Store\events;

/**
 * Class OrderEvent
 * @package DotPlant\Store\events
 */
class OrderEvent extends StoreEvent
{
    /**
     * @var integer the order id
     */
    public $orderId;
}
