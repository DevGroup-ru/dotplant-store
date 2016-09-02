<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DotPlant\Store\models\price\ProductPrice;

/**
 * Class GoodsProduct
 *
 * @package DotPlant\Store\models
 */
class Product extends Goods
{
    use EntityTrait;
    use SeoTrait;

    protected $priceClass = ProductPrice::class;

    protected $visibilityType = null;
    protected $isMeasurable = null;
    protected $isDownloadable = null;
    protected $isFilterable = null;
    protected $isService = null;
    protected $isOption = null;
    protected $isPart = null;
    protected $hasOptions = null;
}
