<?php

namespace DotPlant\Store\interfaces;
use DotPlant\Store\exceptions\GoodsException;
use DotPlant\Store\models\goods\Goods;

/**
 * Interface CalculatorInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface CalculatorInterface
{
    /**
     * Calculates product price according to product type
     *
     * @param Goods $goods
     * @throws GoodsException
     * @return float unformatted product price
     */
    public function calculate(Goods $goods);
}