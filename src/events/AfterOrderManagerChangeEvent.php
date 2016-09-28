<?php

namespace DotPlant\Store\events;

/**
 * Class AfterOrderManagerChangeEvent
 * @package DotPlant\Store\events
 */
class AfterOrderManagerChangeEvent extends OrderEvent
{
    /**
     * @var integer the actual order manager id
     */
    public $managerId;

    /**
     * @var integer the old manager status id
     */
    public $oldManagerId;
}
