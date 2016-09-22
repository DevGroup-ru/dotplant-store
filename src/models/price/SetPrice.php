<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\SetGoodsCalculator;

/**
 * Class SetPrice
 *
 * @package DotPlant\Store\models\price
 */
class SetPrice extends Price
{
    protected $_calculatorClass = SetGoodsCalculator::class;
}
