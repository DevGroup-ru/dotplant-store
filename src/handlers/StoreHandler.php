<?php

namespace DotPlant\Store\handlers;

use DotPlant\Store\events\RetailCheckEvent;

/**
 * Class StoreHandler
 * @package DotPlant\Store\handlers
 */
class StoreHandler
{
    /**
     * @param RetailCheckEvent $event
     */
    public static function DummyRetailCheck(RetailCheckEvent $event)
    {
        if (isset($event->data['isRetail'])) {
            $event->isRetail = $event->data['isRetail'];
        }
    }
}
