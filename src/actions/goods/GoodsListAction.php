<?php

namespace DotPlant\Store\actions\goods;

use DevGroup\AdminUtils\actions\BaseAdminAction;
use DotPlant\Store\models\goods\Goods;
use Yii;

/**
 * Class GoodsListAction
 *
 * @package app\vendor\dotplant\store\src\actions\goods
 */
class GoodsListAction extends BaseAdminAction
{
    public function run()
    {
        $params = Yii::$app->request->get();
        $categoryId = null;
        if (true === isset($params['id'])) {
            $categoryId = (int)$params['id'];
        }
        $searchModel = new Goods();
        $dataProvider = $searchModel->search($params, $categoryId);
        return $this->controller->render(
            '@DotPlant/Store/views/goods-manage/index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }
}