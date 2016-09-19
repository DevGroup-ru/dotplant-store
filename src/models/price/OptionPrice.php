<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\OptionCalculator;

/**
 * Class OptionPrice
 *
 * @package DotPlant\Store\models\price
 */
class OptionPrice extends Price
{
    protected $_calculatorClass = OptionCalculator::class;
}
