<?php

namespace DotPlant\Store\handlers;

use DotPlant\EntityStructure\interfaces\AdditionalRouteHandlerInterface;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\goods\Goods;
use Yii;
use yii\base\Object;
use yii\caching\TagDependency;

class GoodsRouteHandler extends Object implements AdditionalRouteHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function parseUrl($structureId, $slugs)
    {
        $key = "GoodsRoute:$structureId:$slugs[0]";
        $result = Yii::$app->cache->get($key);
        if ($result) {
            return $result;
        }
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
        $result = $modelId !== false && count($slugs) === 1
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
        Yii::$app->cache->set($key, $result, 86400, new TagDependency([
            'tags' => [
                BaseStructure::commonTag()
            ]
        ]));
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($route, $params, $url)
    {
        //! @todo add cache here
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
