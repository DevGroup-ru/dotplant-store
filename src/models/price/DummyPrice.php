<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\goods\Goods;

class DummyPrice implements PriceInterface
{

    public static function convert($from, $to)
    {
        // TODO: Implement convert() method.
    }

    /**
     * @param Goods $goods
     * @return Price
     */
    public static function create(Goods $goods)
    {
        // TODO: Implement create() method.
    }

    public static function format($price, $format)
    {
        // TODO: Implement format() method.
    }

    public function getPrice()
    {
        // TODO: Implement getPrice() method.
    }

    public function setPrice($price)
    {
        // TODO: Implement setPrice() method.
    }
}
