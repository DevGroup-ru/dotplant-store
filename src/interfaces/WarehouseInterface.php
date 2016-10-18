<?php

namespace DotPlant\Store\interfaces;

interface WarehouseInterface
{
    /**
     * @param $goodsId
     * @param $warehouseId
     * @param bool $asArray
     * @return array|\DotPlant\Store\models\warehouse\GoodsWarehouse|WarehouseTypeInterface
     */
    public static function getWarehouse($goodsId, $warehouseId, $asArray = true);

    /**
     * Get warehouses list
     * @param integer $goodsId
     * @param boolean $asArray
     * @return \DotPlant\Store\models\warehouse\GoodsWarehouse[]|array
     */
    public static function getWarehouses($goodsId, $asArray = true);

    /**
     * Is the goods available to order?
     * @param integer $goodsId
     * @return boolean
     */
    public static function isAvailable($goodsId);

    /**
     * Get status code for single goods
     * @param integer $goodsId
     * @return integer the status code
     */
    public static function getStatusCode($goodsId);

}
