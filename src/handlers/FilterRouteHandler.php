<?php

namespace DotPlant\Store\handlers;

use DotPlant\EntityStructure\interfaces\AdditionalRouteHandlerInterface;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\filters\StructureFilterSets;
use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class FilterRouteHandler
 * @package DotPlant\Store\handlers
 */
class FilterRouteHandler extends Object implements AdditionalRouteHandlerInterface
{
    public $route = 'universal/show';

    /**
     * @inheritdoc
     */
    public function createUrl($route, $params, $url)
    {
        if (isset($params['properties']) && count($params['properties']) > 0) {
            // @todo: add a right sort ordering
            // @todo: friendly URL will be allowed for non-multiple (only one value for each property) filter
            $baseStructureId = $this->getBaseStructureId($params);
            if ($baseStructureId !== false) {
                $slugs = $this->getSlugs($baseStructureId, $params['properties']);
                if ($slugs !== false) {
                    return [
                        'isHandled' => true,
                        'preventNextHandler' => true,
                        'url' => implode('/', ArrayHelper::merge([$url], $slugs)),
                    ];
                }
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
            Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSetsByParent'],
                ['entityId' => $structureId]
            ),
            Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSets'],
                ['entityId' => $structureId]
            )
        );
        foreach ($filterSets as $indx => $filterSet) {
            $filterSetValueIndx = $filterSet->getFilterValueIndxBySlug($slug);
            if ($filterSetValueIndx !== null) {
                list($filterSetId, $staticValueId) = explode('.', $filterSetValueIndx);
                list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
                return ['property_id' => $propertyId, 'static_value_id' => $staticValueId];
            }
        }
        return null;
    }

    private function getBaseStructureId($params)
    {
        $structureIds = ArrayHelper::getValue($params, implode('.', ['entities', BaseStructure::class]), []);
        if (count($structureIds) === 1) {
            return reset($structureIds);
        }
        return false;
    }

    private function getSlugs($baseStructureId, $properties)
    {
        /**
         * @var StructureFilterSets[] $filterSets
         */
        $filterSets = ArrayHelper::merge(
            Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSetsByParent'],
                ['entityId' => $baseStructureId]
            ),
            Yii::$container->invoke(
                [StructureFilterSets::class, 'getAttachedFilterSets'],
                ['entityId' => $baseStructureId]
            )
        );
        $slugs = [];
        foreach ($properties as $propertyId => $values) {
            foreach ($filterSets as $filterSet) {
                if ($filterSet->getPropertyId() == $propertyId) {
                    $newSlugs = $filterSet->getSlugsByStaticValuesIds($values);
                    if ($slugs === false) {
                        return false;
                    }
                    $slugs = ArrayHelper::merge($slugs, $newSlugs);
                    break 2;
                }
            }
        }
        return $slugs;
    }
}
