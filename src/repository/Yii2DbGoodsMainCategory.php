<?php

namespace DotPlant\Store\repository;

use DotPlant\EntityStructure\models\BaseStructure;
use yii\db\Query;

class Yii2DbGoodsMainCategory implements GoodsMainCategoryRepositoryInterface
{

    /**
     * @var int
     */
    private $goodsId;

    /**
     * @var null|BaseStructure
     */
    private $goodsMainCategoryByContext = [];

    const TABLE_NAME = '{{%dotplant_store_goods_main_structure}}';

    function __construct($goodsId)
    {
        $this->goodsId = $goodsId;
    }

    /**
     * @param int $contextId
     * @param bool $force
     *
     * @return BaseStructure
     */
    public function loadGoodsMainCategory($contextId, $force = false)
    {
        if ($force || empty($this->goodsMainCategoryByContext[$contextId])) {
            $this->goodsMainCategoryByContext[$contextId] = BaseStructure::find()->where(
                [
                    'context_id' => $contextId,
                    'id' => ((new Query())->select('main_structure_id')->from(self::TABLE_NAME)->where(
                        ['context_id' => $contextId, 'goods_id' => $this->goodsId]
                    )),
                ]
            )->one();
        }
        return $this->goodsMainCategoryByContext[$contextId];
    }

    /**
     * @param BaseStructure $mainCategory
     * @param $contextId
     */
    public function setMainCategory(BaseStructure $mainCategory, $contextId)
    {
        \Yii::$app->db->createCommand()->delete(
            self::TABLE_NAME,
            ['context_id' => $contextId, 'goods_id' => $this->goodsId]
        )->execute();
        \Yii::$app->db->createCommand()->insert(
            self::TABLE_NAME,
            ['context_id' => $contextId, 'goods_id' => $this->goodsId, 'main_structure_id' => $mainCategory->id]
        )->execute();
        $this->loadGoodsMainCategory($contextId, true);
    }
}
