<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DotPlant\Store\models\price\BundlePrice;

/**
 * Class GoodsBundle
 *
 * @package DotPlant\Store\models
 */
class Bundle extends Goods
{

    protected $priceClass = BundlePrice::class;

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
