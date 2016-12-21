<?php

namespace DotPlant\Store\models\filters;

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
}
