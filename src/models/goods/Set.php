<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DotPlant\Store\models\price\SetPrice;

/**
 * Class GoodsSet
 *
 * @package DotPlant\Store\models
 */
class Set extends Goods
{
    use EntityTrait;
    use SeoTrait;

    public $_priceClass = SetPrice::class;

    public $_visibilityType = null;
    public $_isMeasurable = null;
    public $_isDownloadable = null;
    public $_isFilterable = null;
    public $_isService = null;
    public $_isOption = null;
    public $_isPart = null;
    public $_hasOptions = null;
}
