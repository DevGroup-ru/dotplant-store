<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\FileGoodsCalculator;

/**
 * Class FilePrice
 *
 * @package DotPlant\Store\models\price
 */
class FilePrice extends Price
{
    protected $_calculatorClass = FileGoodsCalculator::class;
}
