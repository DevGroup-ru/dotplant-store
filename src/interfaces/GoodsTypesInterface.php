<?php

namespace DotPlant\Store\interfaces;

/**
 * Interface GoodsTypesInterface
 *
 * @package DotPlant\Store\interfaces
 */
interface GoodsTypesInterface
{
    const TYPE_PRODUCT = 0;
    const TYPE_BUNDLE = 1;
    const TYPE_SET = 2;
    const TYPE_PART = 3;
    const TYPE_OPTION = 4;
    const TYPE_SERVICE = 5;
    const TYPE_FILE = 6;

    /**
     * Returns all acceptable product types
     *
     * @return array
     */
    public static function getTypes();

    /**
     * Returns all acceptable product roles
     *
     * Role is the same with type, but roles can be applicable just in few cases
     * and only few or types sre roles
     *
     * @return array
     */
    public static function getRoles();

    /**
     * Returns product type
     * If product has role, role to be returned as type
     *
     * @return int
     */
    public function getType();

    /**
     * Sets product type
     *
     * @param $type
     * @return bool
     */
    public function setType($type);

    /**
     * Sets product role
     *
     * @param $role
     * @return bool
     */
    public function setRole($role);
}