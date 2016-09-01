<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\ServiceCalculator;

/**
 * Class ServicePrice
 *
 * @package DotPlant\Store\models\price
 */
class ServicePrice extends Price
{
    protected $_calculatorClass = ServiceCalculator::class;
}