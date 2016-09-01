<?php

namespace DotPlant\Store\interfaces;

interface WarehouseTypeInterface
{
    /**
     * Reserve goods
     * @param $goodsId integer
     * @param $quantity integer
     * @return mixed
     */
    public function reserve($goodsId, $quantity);

    /**
     * Release goods
     * @param $goodsId integer
     * @param $quantity integer
     * @return mixed
     */
    public function release($goodsId, $quantity);

    /**
     * Reduce goods
     * @param $goodsId integer
     * @param $quantity integer
     * @return mixed
     */
    public function reduce($goodsId, $quantity);
}
