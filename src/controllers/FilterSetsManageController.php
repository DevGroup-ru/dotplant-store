<?php

namespace DotPlant\Store\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use yii\filters\AccessControl;

class FilterSetsManageController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['dotplant-store-filter-sets-view'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($id = null)
    {
        return $this->render('index', ['parentId' => $id,]);
    }
}
