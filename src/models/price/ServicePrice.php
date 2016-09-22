<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\ServiceGoodsCalculator;

/**
 * Class ServicePrice
 *
 * @package DotPlant\Store\models\price
 */
class ServicePrice extends Price
{
    protected $_calculatorClass = ServiceGoodsCalculator::class;
}
