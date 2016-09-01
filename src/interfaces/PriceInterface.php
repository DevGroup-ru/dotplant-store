<?php

namespace DotPlant\Store\interfaces;

use DotPlant\Store\models\price\Price;

/**
 * Interface PriceInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface PriceInterface
{
    /**
     * @return Price
     */
    public static function create();

    public function getPrice();

    public function setPrice($price);

    public static function convert($from, $to);

    public static function format($price, $format);
}