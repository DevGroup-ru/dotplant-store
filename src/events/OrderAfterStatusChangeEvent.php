<?php

namespace DotPlant\Store\events;

/**
 * Class OrderAfterStatusChangeEvent
 * @package DotPlant\Store\events
 */
class OrderAfterStatusChangeEvent extends OrderEvent
{
    /**
     * @var integer the actual order status id
     */
    public $statusId;

    /**
     * @var integer the old order status id
     */
    public $oldStatusId;
}
