<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\BundleGoodsCalculator;

/**
 * Class BundlePrice
 * @package DotPlant\Store\models\price
 */
class BundlePrice extends Price
{
    protected $_calculatorClass = BundleGoodsCalculator::class;
}
