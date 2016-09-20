<?php


namespace DotPlant\Store\actions\extendedPrice;


use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ExtendedPriceAjaxFormAction extends Action
{
    public function run()
    {
        if (Yii::$app->request->isAjax === false) {
            throw new ForbiddenHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $post = Yii::$app->request->post();
        $json = Json::decode(ArrayHelper::getValue($post, ['extendedPriceRule']));
        $extendedPriceRule = ExtendedPriceRule::loadModel(ArrayHelper::getValue($json, ['id']), true);
        $extendedPriceRule->load(['extendedPriceRule' => $json], 'extendedPriceRule');

        $extendedPrice = ExtendedPrice::loadModel($extendedPriceRule->extended_price_id, true);
        $extendedPrice->load($post);
        $extendedPrice->save();

        $extendedPriceRule->extended_price_id = $extendedPrice->id;
        $extendedPriceRule->save();

        return ['success' => !$extendedPrice->isNewRecord, 'extra' => ['extendedPriceRule' => $extendedPriceRule]];
    }
}