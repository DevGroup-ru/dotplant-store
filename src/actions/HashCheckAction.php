<?php


namespace DotPlant\Store\actions;


use DevGroup\Frontend\Universal\ActionData;
use DevGroup\Frontend\Universal\UniversalAction;
use yii\web\BadRequestHttpException;

class HashCheckAction extends UniversalAction
{

    /**
     * @param ActionData $actionData
     *
     * @throws BadRequestHttpException
     */
    public function run(&$actionData)
    {
        $hash = \Yii::$app->request->get('hash');
        if (is_null($hash)) {
            throw new BadRequestHttpException;
        }
    }
}