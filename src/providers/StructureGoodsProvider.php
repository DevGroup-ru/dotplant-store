<?php

namespace DotPlant\Store\providers;

use DevGroup\Frontend\Universal\ActionData;
use yii\helpers\VarDumper;

class StructureGoodsProvider extends BaseGoodsProvider
{
    public function getEntities(&$actionData)
    {
        VarDumper::dump($this->getGoods([3]), 10, 1);
        die;
        return [];
    }

    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }
}
