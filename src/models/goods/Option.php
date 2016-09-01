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
    use EntityTrait;
    use SeoTrait;

    public $_priceClass = OptionPrice::class;

    public $_visibilityType = null;
    public $_isMeasurable = null;
    public $_isDownloadable = null;
    public $_isFilterable = null;
    public $_isService = null;
    public $_isOption = null;
    public $_isPart = null;
    public $_hasOptions = null;
}
