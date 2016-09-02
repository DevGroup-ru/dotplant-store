<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DotPlant\Store\models\price\ServicePrice;

/**
 * Class GoodsService
 *
 * @package DotPlant\Store\models
 */
class Service extends Goods
{
    use EntityTrait;
    use SeoTrait;

    protected $priceClass = ServicePrice::class;

    protected $visibilityType = null;
    protected $isMeasurable = null;
    protected $isDownloadable = null;
    protected $isFilterable = null;
    protected $isService = null;
    protected $isOption = null;
    protected $isPart = null;
    protected $hasOptions = null;
}
