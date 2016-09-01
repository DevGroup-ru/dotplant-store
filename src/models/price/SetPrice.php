<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\SetCalculator;

/**
 * Class SetPrice
 *
 * @package DotPlant\Store\models\price
 */
class SetPrice extends Price
{
    protected $_calculatorClass = SetCalculator::class;
}