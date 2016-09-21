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
    protected $priceClass = ProductPrice::class;

    protected $visibilityType = true;
    protected $hasChild = false;
    protected $isMeasurable = true;
    protected $isFilterable = true;
    protected $isDownloadable = false;
    protected $isService = false;
    protected $isOption = false;
    protected $isPart = true;
    protected $hasOptions = true;


}
