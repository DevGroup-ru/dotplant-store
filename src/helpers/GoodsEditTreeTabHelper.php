<?php

namespace DotPlant\Store\helpers;

use DevGroup\Multilingual\interfaces\ContentTabHandlerInterface;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\Store\models\goods\Goods;
use yii\widgets\ActiveForm;

class GoodsEditTreeTabHelper implements ContentTabHandlerInterface
{
    /**
     * @var Goods
     */
    private $goods;
    /**
     * @var null|int
     */
    private $startCategoryId;
    /**
     * @var ActiveForm
     */
    private $form;

    public function __construct(Goods $goods, $startCategoryId, ActiveForm $form)
    {
        $this->goods = $goods;
        $this->startCategoryId = $startCategoryId;
        $this->form = $form;
    }

    /**
     * @param int $contextId
     *
     * @return array [$key => $data]
     */
    public function contextData($contextId)
    {
        $checked = array_merge(
            empty($this->startCategoryId) ? [] : [$this->startCategoryId],
            CategoryGoods::getBindings($this->goods->id, $contextId)
        );
        return ['tree' => ['goods' => $this->goods, 'checked' => $checked, 'form' => $this->form]];
    }
}
