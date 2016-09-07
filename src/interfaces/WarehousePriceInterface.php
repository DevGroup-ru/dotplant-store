<?php

namespace DotPlant\Store\interfaces;

interface WarehousePriceInterface
{
    /**
     * Get price by price type
     * @param $priceType
     * @return double
     */
    public function getPrice($priceType);
}
