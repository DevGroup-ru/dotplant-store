<?php

namespace DotPlant\Store\models\filters;

class StructureFilterSets
{
    private $structure_id;
    private $property_id;
    private $sort_order;
    private $delegate_to_child;
    private $group_id;
    private $filter_values;

    /**
     * StructureFilterSets constructor.
     *
     * @param int $structure_id
     * @param int $property_id
     * @param int $sort_order
     * @param bool $delegate_to_child
     * @param int $group_id
     * @param StructureFilterValue[] $filter_values
     */
    function __construct($structure_id, $property_id, $sort_order, $delegate_to_child, $group_id, array $filter_values)
    {
        $this->structure_id = $structure_id;
        $this->property_id = $property_id;
        $this->sort_order = $sort_order;
        $this->delegate_to_child = $delegate_to_child;
        $this->group_id = $group_id;
        $this->filter_values = $filter_values;
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
            $this->filter_values
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
        $this->filter_values[] = $value;
    }

    /**
     * @param StructureFilterValue $value
     */
    public function removeFilterValue(StructureFilterValue $value)
    {
        if (!$this->hasFilterValue($value)) {
            throw new \InvalidArgumentException('Nothing to remove');
        }
        foreach ($this->filter_values as $key => $filterValue) {
            if ($filterValue->isEqual($value)) {
                unset($this->filter_values[$key]);
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
        foreach ($this->filter_values as $filterValue) {
            if ($filterValue->isEqual($value)) {
                return true;
            }
        }
        return false;
    }
}
