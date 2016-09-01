<?php

namespace DotPlant\Store\models\product;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;

/**
 * Class GoodsBundle
 *
 * @package DotPlant\Store\models
 */
class GoodsBundle extends Goods
{
    use EntityTrait;
    use SeoTrait;

    public $priceClass = null;
    public $visibilityType = null;
    public $isMeasurable = null;
    public $isDownloadable = null;
    public $isFilterable = null;
    public $isService = null;
    public $isOption = null;
    public $isPart = null;
    public $hasOptions = null;
}