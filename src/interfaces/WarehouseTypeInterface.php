<?php

namespace DotPlant\Store\interfaces;

interface WarehouseTypeInterface
{
    /**
     * Reserve goods
     * @param $quantity integer
     * @return mixed
     */
    public function reserve($quantity);

    /**
     * Release goods
     * @param $quantity integer
     * @return mixed
     */
    public function release($quantity);

    /**
     * Reduce goods
     * @param $quantity integer
     * @return mixed
     */
    public function reduce($quantity);
}
