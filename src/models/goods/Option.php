<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DotPlant\Store\models\price\OptionPrice;

/**
 * Class GoodsOption
 *
 * @package DotPlant\Store\models
 */
class Option extends Goods
{

    protected $priceClass = OptionPrice::class;

    protected $visibilityType = null;
    protected $isMeasurable = true;
    protected $isDownloadable = null;
    protected $isFilterable = false;
    protected $isService = null;
    protected $isOption = true;
    protected $isPart = null;
    protected $hasOptions = null;
    protected $hasChild = false;
}
