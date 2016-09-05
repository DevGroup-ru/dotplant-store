<?php

namespace DotPlant\Store\handlers;

use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;

class OrderHandler
{
    public static function afterUserCurrencyChange(AfterUserCurrencyChangeEvent $event)
    {
        /**
         * @todo: recalculate cart if user changes a currency
         */
    }

    public static function afterUserLogin()
    {
        /**
         * @todo: set created_by for cart model after login
         * @todo: set created_by for order model after login
         */
    }
}
