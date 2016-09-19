<?php


namespace DotPlant\Store\widgets\backend;

use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use Yii;
use yii\base\Widget;

class ExtendedPriceGrid extends Widget
{
    public $model;

    public function run()
    {
        $searchModel = new ExtendedPrice;
        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);
        return $this->render(
            'extended-price-grid',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'targetModel' => $this->model,
            ]
        );
    }
}
