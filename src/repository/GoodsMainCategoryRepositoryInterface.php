<?php


namespace DotPlant\Store\repository;


use DotPlant\EntityStructure\models\BaseStructure;

interface GoodsMainCategoryRepositoryInterface
{
    /**
     * GoodsMainCategoryRepositoryInterface constructor.
     *
     * @param $goodsId
     */
    public function __construct($goodsId);

    /**
     * @param $contextId
     *
     * @return BaseStructure
     */
    public function loadGoodsMainCategory($contextId);

    /**
     * @param BaseStructure $mainCategory
     * @param $contextId
     */
    public function setMainCategory(BaseStructure $mainCategory, $contextId);
}