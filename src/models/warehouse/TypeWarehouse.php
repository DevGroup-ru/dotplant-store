<?php

namespace DotPlant\Store\models\warehouse;

use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\interfaces\WarehouseTypeInterface;
use yii\helpers\ArrayHelper;

class TypeWarehouse extends GoodsWarehouse implements WarehouseTypeInterface
{
    /**
     * @inheritdoc
     */
    public function reserve($quantity)
    {

        $this->lockForUpdate();
        if (Warehouse::hasEnoughQuantity($this->goods_id, $quantity, $this->warehouse_id) == false) {
            throw new OrderException(\Yii::t('dotplant.store', 'The warehouse has no enough goods'));
        }
        $this->reserved_count += $quantity;
        $this->save();
    }

    /**
     * @inheritdoc
     */
    public function release($quantity)
    {
        $this->lockForUpdate();
        $this->reserved_count -= $quantity;
        $this->save();
    }

    /**
     * @inheritdoc
     */
    public function reduce($quantity)
    {
        $this->lockForUpdate();
        $this->available_count -= $quantity;
        $this->reserved_count -= $quantity;
        $this->save();
    }
}
