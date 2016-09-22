<?php


namespace DotPlant\Store\interfaces;

/**
 * Interface NoGoodsCalculatorInterface
 * @package DotPlant\Store\interfaces
 */
interface NoGoodsCalculatorInterface
{
    /**
     * Calculates object price
     *
     * @param $object
     *
     * @return array
     */
    public static function getPrice($object);
}
