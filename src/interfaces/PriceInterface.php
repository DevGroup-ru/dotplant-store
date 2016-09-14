<?php

namespace DotPlant\Store\interfaces;

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\price\Price;

/**
 * Interface PriceInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface PriceInterface
{

    const TYPE_RETAIL = 'retail';
    const TYPE_WHOLESALE = 'wholesale';
    const TYPE_SELLER = 'seller';

    /**
     * @param Goods $goods
     * @return Price
     */
    public static function create(Goods $goods);

    public function getPrice($warehouseId, $priceType, $withDiscount);

    public function setPrice($price);

    public static function convert($from, $to);

    public static function format($price, $format);

    public function getWarehouseId();

    public function getPriceType();

    public function getGoodsId();
}
