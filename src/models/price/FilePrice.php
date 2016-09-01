<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\FileCalculator;

/**
 * Class FilePrice
 *
 * @package DotPlant\Store\models\price
 */
class FilePrice extends Price
{
    protected $_calculatorClass = FileCalculator::class;
}