<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\BaseActionsInfoTrait;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SoftDeleteTrait;
use DotPlant\EntityStructure\models\BaseStructure;

/**
 * Class GoodsCategory
 *
 * @package DotPlant\Store
 */
class GoodsCategory extends BaseStructure
{
    use EntityTrait;
    use BaseActionsInfoTrait;
    use SoftDeleteTrait;

    const TRANSLATION_CATEGORY = 'dotplant.store';

    protected static $tablePrefix = 'dotplant_store_category';

    protected static function getPageSize()
    {
        //todo place it to the Module
        return 15;
    }
}