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
     *
     * @return mixed
     */
    public function getPrice(
        $warehouseId,
        $priceType = PriceInterface::TYPE_RETAIL,
        $withDiscount = true,
        $convertIsoCode = false
    ) {
        return [];
    }

    public static function convert($from, $to)
    {
        // TODO: Implement convert() method.
    }

    /**
     * @param Goods $goods
     *
     * @return Price
     */
    public static function create(Goods $goods)
    {
        // TODO: Implement create() method.
    }

    public function format($price, $format)
    {
        // TODO: Implement format() method.
    }

    public function setPrice($price)
    {
        // TODO: Implement setPrice() method.
    }

    public function getWarehouseId()
    {
        // TODO: Implement getWarehouseId() method.
    }

    public function getPriceType()
    {
        // TODO: Implement getPriceType() method.
    }

    public function getGoodsId()
    {
        // TODO: Implement getGoodsId() method.
    }
}
