<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\OptionGoodsCalculator;

/**
 * Class OptionPrice
 *
 * @package DotPlant\Store\models\price
 */
class OptionPrice extends Price
{
    protected $_calculatorClass = OptionGoodsCalculator::class;
}
