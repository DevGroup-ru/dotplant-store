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

    /**
     * Lock a current record for update
     * This method has to lock database row for updating and has to update the model attributes.
     */
    public function lockForUpdate();

    /**
     * Get units count
     * @return double
     */
    public function getCount();
}
