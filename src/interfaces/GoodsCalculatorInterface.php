<?php

namespace DotPlant\Store\interfaces;

use DotPlant\Store\exceptions\GoodsException;

/**
 * Interface CalculatorInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface GoodsCalculatorInterface
{
    /**
     * Calculates product price according to product type
     *
     * @param PriceInterface $price
     * @throws GoodsException
     * @return float unformatted product price
     */
    public static function calculate(PriceInterface $price);
}
