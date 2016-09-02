<?php

namespace DotPlant\Store\models\warehouse;

use DotPlant\Store\exceptions\WarehouseException;
use DotPlant\Store\interfaces\WarehouseTypeInterface;

class TypeSeller extends GoodsWarehouse implements WarehouseTypeInterface
{
    public function reserve($goodsId, $quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }

    public function release($goodsId, $quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }

    public function reduce($goodsId, $quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }
}
