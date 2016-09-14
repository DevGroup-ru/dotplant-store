<?php

namespace DotPlant\Store\events;

/**
 * Class RetailCheckEvent
 * @package DotPlant\Store\events
 */
class RetailCheckEvent extends StoreEvent
{
    /**
     * @var bool whether the retail store
     */
    public $isRetail = true;
}
