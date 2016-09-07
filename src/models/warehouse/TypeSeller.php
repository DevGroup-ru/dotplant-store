<?php

namespace DotPlant\Store\models\warehouse;

use DotPlant\Store\exceptions\WarehouseException;
use DotPlant\Store\interfaces\WarehouseTypeInterface;

class TypeSeller extends GoodsWarehouse implements WarehouseTypeInterface
{
    /**
     * @inheritdoc
     */
    public function reserve($quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }

    /**
     * @inheritdoc
     */
    public function release($quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }

    /**
     * @inheritdoc
     */
    public function reduce($quantity)
    {
        throw new WarehouseException(__METHOD__ . ' is not implemented yet');
    }
}
