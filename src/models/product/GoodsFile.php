<?php

namespace DotPlant\Store\models\product;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;

/**
 * Class GoodsFile
 *
 * @package DotPlant\Store\models
 */
class GoodsFile extends Goods
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