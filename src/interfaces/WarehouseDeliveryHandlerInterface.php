<?php

namespace DotPlant\Store\interfaces;


/**
 * Interface WarehouseDeliveryHandlerInterface
 * @package DotPlant\Store\interfaces
 */
interface WarehouseDeliveryHandlerInterface
{

    /**
     * @param array $params
     * @return int
     */
    public static function  getTerm(array $params);
}