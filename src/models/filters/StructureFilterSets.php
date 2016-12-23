<?php

namespace DotPlant\Store\models\filters;

use yii\helpers\ArrayHelper;

class StructureFilterSets
{
    private $structureId;
    private $propertyId;
    private $sortOrder;
    private $delegateToChild;
    private $groupId;
    private $filterValues;
    private $propertyName;
    private $propertyGroupName;

    private $filterSetId;

    /**
     * StructureFilterSets constructor.
     *
     * @param string $property_name
     * @param string $property_group_name
     * @param int $structure_id
     * @param int $property_id
     * @param int $sort_order
     * @param bool $delegate_to_child
     * @param int $group_id
     * @param StructureFilterValue[] $filter_values
     */
    function __construct(
        $property_name,
        $property_group_name,
        $structure_id,
        $property_id,
        $sort_order,
        $delegate_to_child,
        $group_id,
        array $filter_values
    ) {
        $this->structureId = $structure_id;
        $this->propertyId = $property_id;
        $this->sortOrder = $sort_order;
        $this->delegateToChild = $delegate_to_child;
        $this->groupId = $group_id;
        $this->filterValues = $filter_values;
        $this->propertyName = $property_name;
        $this->propertyGroupName = $property_group_name;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @return array
     */
    public function getFilterValuesAsArray()
    {
        return array_map(
            function (StructureFilterValue $value) {
                return $value->getAsArray();
            },
            $this->filterValues
        );
    }

    /**
     * @param StructureFilterValue $value
     */
    public function addFilterValue(StructureFilterValue $value)
    {
        if ($this->hasFilterValue($value)) {
            throw new \InvalidArgumentException('FilterValue already exist');
        }
        $this->filterValues[] = $value;
    }

    /**
     * @param StructureFilterValue $value
     */
    public function removeFilterValue(StructureFilterValue $value)
    {
        if (!$this->hasFilterValue($value)) {
            throw new \InvalidArgumentException('Nothing to remove');
        }
        foreach ($this->filterValues as $key => $filterValue) {
            if ($filterValue->isEqual($value)) {
                unset($this->filterValues[$key]);
            }
        }
    }

    /**
     * @param StructureFilterValue $value
     *
     * @return bool
     */
    public function hasFilterValue(StructureFilterValue $value)
    {
        foreach ($this->filterValues as $filterValue) {
            if ($filterValue->isEqual($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getPropertyGroupName()
    {
        return $this->propertyGroupName;
    }

    /**
     * @return bool
     */
    public function isDelegatableToChildren()
    {
        return $this->delegateToChild;
    }

    /**
     * @param string $slug
     *
     * @return null|string
     */
    public function getFilterValueIndxBySlug($slug)
    {
        foreach ($this->filterValues as $indx => $filterValue) {
            if ($filterValue->getSlug() === $slug) {
                return $indx;
            }
        }
        return null;
    }


    /**
     * @param $entityId
     * @param FiltersRepository $filtersRepository
     * @param bool $createNotInFilter
     *
     * @return array
     */
    public static function getAttachedFilterSets(
        $entityId,
        FiltersRepository $filtersRepository,
        $createNotInFilter = true
    ) {
        $filterSetsFromDb = $filtersRepository->getFilterSetByEntityId($entityId);
        return self::createByDataFromBd($entityId, $filtersRepository, $createNotInFilter, $filterSetsFromDb);
    }

    /**
     * @param $entityId
     * @param FiltersRepository $filtersRepository
     *
     * @return array|static[]
     */
    public static function getAttachedFilterSetsByParent($entityId, FiltersRepository $filtersRepository)
    {
        $filterSetsFromDb = $filtersRepository->getDelegatedByParentFilterSets($entityId);
        return self::createByDataFromBd($entityId, $filtersRepository, false, $filterSetsFromDb);
    }

    /**
     * @param $entityId
     * @param FiltersRepository $filtersRepository
     * @param $createNotInFilter
     * @param $filterSetsFromDb
     *
     * @return array|static[]
     */
    private static function createByDataFromBd(
        $entityId,
        FiltersRepository $filtersRepository,
        $createNotInFilter,
        $filterSetsFromDb
    ) {
        $sets = [];
        foreach ($filterSetsFromDb as $filterSetFromDb) {
            $filterSetValuesFromDb = $filtersRepository->getFilterStaticValuesByFilterSet($filterSetFromDb);
            if ($createNotInFilter) {
                $filterStaticValuesIds = ArrayHelper::getColumn($filterSetValuesFromDb, 'static_value_id');
                $staticValuesNotInFilter = $filtersRepository->getStaticValuesNotInFilter(
                    $filterStaticValuesIds,
                    $filterSetFromDb
                );
                if (count($staticValuesNotInFilter) > 0) {
                    foreach ($staticValuesNotInFilter as $staticValue) {
                        $model = $filtersRepository->createFilterStaticValue($staticValue, $filterSetFromDb);
                        $filterSetValuesFromDb[] = $model;
                    }
                }
            }
            $values = [];
            foreach ($filterSetValuesFromDb as $filterSetValueFromDb) {
                $indx = implode('.', [$filterSetValueFromDb->filter_set_id, $filterSetValueFromDb->static_value_id]);
                $values[$indx] = (new StructureFilterValue(
                    $filterSetValueFromDb->staticValue->name,
                    $filterSetValueFromDb->staticValue->slug,
                    $filterSetValueFromDb->sort_order,
                    boolval($filterSetValueFromDb->display)
                ));
            }
            $indx = "$entityId.{$filterSetFromDb->group->id}.{$filterSetFromDb->property->id}";
            $set = new static(
                $filterSetFromDb->property->name,
                $filterSetFromDb->group->internal_name,
                $entityId,
                $filterSetFromDb->property_id,
                $filterSetFromDb->sort_order,
                boolval($filterSetFromDb->delegate_to_child),
                $filterSetFromDb->group_id,
                $values
            );
            $set->filterSetId = $filterSetFromDb->id;
            $sets[$indx] = $set;
        }
        return $sets;
    }

    /**
     * @return int
     */
    public function getPropertyId()
    {
        return $this->propertyId;
    }

    public function getSlugsByStaticValuesIds($values)
    {
        $slugs = [];
        foreach ($values as $value) {
            $indx = implode('.', [$this->filterSetId, $value]);
            if (!array_key_exists($indx, $this->filterValues)) {
                return false;
            }
            $slugs[] = $this->filterValues[$indx]->getSlug();
        }
        return $slugs;
    }

}
