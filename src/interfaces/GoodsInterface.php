<?php

namespace DotPlant\Store\interfaces;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\exceptions\GoodsException;

/**
 * Interface GoodsInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface GoodsInterface
{
    /**
     * Fabric method to instantiate fully configured goods
     *
     * @param int $type
     * @returns Goods
     * @throws GoodsException
     */
    public static function create($type);
}