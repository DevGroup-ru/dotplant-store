<?php

namespace DotPlant\Store\handlers;

use DevGroup\DataStructure\models\StaticValue;
use DotPlant\EntityStructure\interfaces\AdditionalRouteHandlerInterface;
use DotPlant\Store\models\filters\StructureFilterSets;
use DotPlant\Store\models\filters\StructureFilterValue;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class FilterRouteHandler
 * @package DotPlant\Store\handlers
 */
class FilterRouteHandler extends Object implements AdditionalRouteHandlerInterface
{
    public $route = 'universal/show-with-properties';

    /**
     * @inheritdoc
     */
    public function createUrl($route, $params, $url)
    {
        if (isset($params['properties']) && count($params['properties']) > 0) {
            // @todo: add a right sort ordering
            // @todo: friendly URL will be allowed for non-multiple (only one value for each property) filter
            $condition = ['or'];
            $valuesCount = 0;
            foreach ($params['properties'] as $propertyId => $values) {
                $condition[] = [
                    'property_id' => $propertyId,
                    'model_id' => $values,
                ];
                $valuesCount += count($values);
            }
            $slugs = StaticValue::find()->select(['slug'])->where(
                [
                    'and',
                    ['language_id' => 1],
                    $condition,
                ]
            )->orderBy(
                [
                    'property_id' => SORT_ASC,
                    'model_id' => SORT_ASC,
                ]
            )->asArray(true)->column();
            if (count($slugs) == $valuesCount) {
                return [
                    'isHandled' => true,
                    'preventNextHandler' => true,
                    'url' => $url . '/' . implode('/', $slugs),
                ];
            }
        }
        return ['isHandled' => false];
    }

    /**
     * @inheritdoc
     */
    public function parseUrl($structureId, $slugs)
    {
        // @todo: check a slugs ordering
        $properties = [];
        foreach ($slugs as $index => $slug) {
            $row = $this->getStructureFilterValue($structureId, $slug);
            if ($row === null) {
                return ['isHandled' => false];
            }
            if (!isset($properties[$row['property_id']])) {
                $properties[$row['property_id']] = [];
            }
            $properties[$row['property_id']][] = $row['static_value_id'];
        }
        return [
            'isHandled' => true,
            'preventNextHandler' => false,
            'slugs' => [],
            'route' => $this->route,
            'routeParams' => [
                'properties' => $properties,
            ],
        ];
    }

    /**
     * @param integer $structureId
     * @param string $slug
     *
     * @return array|null
     */
    private function getStructureFilterValue($structureId, $slug)
    {
        /**
         * @var StructureFilterSets[] $filterSets
         */
        $filterSets = ArrayHelper::merge(
            \Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSetsByParent'],
                ['entityId' => $structureId]
            ),
            \Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSets'],
                ['entityId' => $structureId]
            )
        );
        foreach ($filterSets as $indx => $filterSet) {
            $filterSetValueIndx = $filterSet->getFilterValueIndxBySlug($slug);
            if($filterSetValueIndx !== null){
                list($filterSetId, $staticValueId) = explode('.', $filterSetValueIndx);
                list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
                return ['property_id' => $propertyId, 'static_value_id' => $staticValueId];
            }
        }
        return null;
    }
}
