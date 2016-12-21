<?php

namespace DotPlant\Store\models\filters;

class StructureFilterValue
{
    private $value;
    private $slug;
    private $sort_order;
    private $display;

    /**
     * StructureFilterValue constructor.
     *
     * @param string $value
     * @param string $slug
     * @param int $sort_order
     * @param bool $display
     */
    public function __construct($value, $slug, $sort_order, $display)
    {
        $this->value = $value;
        $this->slug = $slug;
        $this->sort_order = $sort_order;
        $this->display = $display;
    }

    /**
     * @return array
     */
    public function getAsArray()
    {
        return [
            'value' => $this->value,
            'slug' => $this->slug,
            'sort_order' => $this->sort_order,
            'display' => $this->display,
        ];
    }

    public function isEqual(StructureFilterValue $value)
    {
        return $this->value === $value->value && $this->slug === $value->slug && $this->sort_order === $value->sort_order && $this->display === $value->display;
    }
}
