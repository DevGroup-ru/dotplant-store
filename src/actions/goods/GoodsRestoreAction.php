<?php

namespace DotPlant\Store\actions\goods;

use DevGroup\AdminUtils\actions\BaseAdminAction;
use DotPlant\Store\models\goods\Goods;
use yii\web\NotFoundHttpException;
use Yii;

class GoodsRestoreAction extends BaseAdminAction
{
    public function run($product_id, $returnUrl)
    {
        /** @var Goods $model */
        $model = Goods::loadModel(
            $product_id,
            true,
            false,
            86400,
            new NotFoundHttpException(
                Yii::t('app', "{model} with id :'{id}' not found!", [
                    'model' => Yii::t('app', 'Goods'),
                    'id' => $product_id
                ])
            )
        );

        $model->restore() !== false ?
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been restored')) :
            Yii::$app->session->setFlash('error', Yii::t('app', 'Object has not been restored'));

        return $this->controller->redirect($returnUrl);
    }
}
