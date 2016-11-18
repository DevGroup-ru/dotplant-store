<?php

namespace DotPlant\Store\models\goods;

use DotPlant\Store\models\price\SetPrice;

/**
 * Class GoodsSet
 *
 * @package DotPlant\Store\models
 */
class Set extends Goods
{
    protected $priceClass = SetPrice::class;

    protected $visibilityType = true;
    protected $hasChild = true;
    protected $isMeasurable = false;
    protected $isFilterable = true;
    protected $isDownloadable = true;
    protected $isService = true;
    protected $isOption = true;
    protected $isPart = true;
    protected $hasOptions = true;
}
