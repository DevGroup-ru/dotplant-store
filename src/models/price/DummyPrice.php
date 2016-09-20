<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\goods\Goods;

class DummyPrice extends Price
{

    /**
     * @param $warehouseId
     * @param string $priceType
     * @param bool|true $withDiscount
     * @param bool|false $convertIsoCode
     * @return mixed
     */
    public function getPrice($warehouseId, $priceType = PriceInterface::TYPE_RETAIL, $withDiscount = true, $convertIsoCode = false)
    {
        return [];
    }
}
