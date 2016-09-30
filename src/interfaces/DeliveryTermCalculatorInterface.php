<?php

namespace DotPlant\Store\interfaces;


/**
 * Interface DeliveryTermCalculatorInterface
 * @package DotPlant\Store\interfaces
 */
interface DeliveryTermCalculatorInterface
{
    /**
     * @param $object
     * @return int
     */
    public static function getDeliveryTerm($object);
}