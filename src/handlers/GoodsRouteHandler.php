<?php

namespace DotPlant\Store\handlers;

use DotPlant\EntityStructure\interfaces\AdditionalRouteHandlerInterface;
use DotPlant\Store\models\goods\Goods;
use yii\base\Object;

class GoodsRouteHandler extends Object implements AdditionalRouteHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function parseUrl($structureId, $slugs)
    {
        $modelId = Goods::find()
            ->select(['id'])
            ->where(
                [
                    // @todo: Use SQL index
                    'main_structure_id' => $structureId,
                    'slug' => $slugs[0],
                    'is_active' => 1,
                    'is_deleted' => 0,
                ]
            )
            ->scalar();
        return $modelId !== false && count($slugs) === 1
            ? [
                'isHandled' => true,
                'preventNextHandler' => true,
                'route' => 'store/goods/show',
                'routeParams' => [
                    'entities' => [
                        Goods::class => [
                            $modelId,
                        ],
                    ],
                ],
                'slugs' => [],

            ]
            : ['isHandled' => false];
    }

    /**
     * @inheritdoc
     */
    public function createUrl($route, $params, $url)
    {
        if (!isset($params['entities'][Goods::class])) {
            return false;
        }
        $goods = Goods::get(reset($params['entities'][Goods::class]));
        if ($goods === null) {
            return false;
        }
        return [
            'isHandled' => true,
            'preventNextHandler' => true,
            'url' => $url . '/' . $goods->slug,
        ];
    }
}
