<?php

namespace DotPlant\Store\handlers\warehouseDelivery;

use DotPlant\Store\interfaces\WarehouseDeliveryHandlerInterface;

/**
 * Class WarehouseFixedDeliveryHandler
 * @package DotPlant\Store\handlers\warehouseDelivery
 */
class FixedWarehouseDeliveryHandler implements WarehouseDeliveryHandlerInterface
{
    /**
     * @param $params
     * @return int
     */
    public static function getTerm(array $params)
    {
        return isset($params['deliveryTerm']) ? (int) $params['deliveryTerm'] : 0;
    }
}
