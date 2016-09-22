<?php

namespace DotPlant\Store\interfaces;

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\exceptions\GoodsException;
use yii\web\NotFoundHttpException;

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
     *
     * @returns Goods
     * @throws GoodsException
     */
    public static function create($type);

    /**
     * Returns properly instantiated Goods model if found
     *
     * @param int $id
     *
     * @return Goods
     * @throws GoodsException
     */
    public static function get($id);

    public function getPrice($warehouseId, $priceType, $withDiscount, $convertIsoCode);
}
