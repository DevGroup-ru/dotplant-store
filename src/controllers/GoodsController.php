<?php

namespace DotPlant\Store\controllers;

use DevGroup\Frontend\controllers\FrontendController;
use DevGroup\Frontend\Universal\Core\FillEntities;
use DevGroup\Frontend\Universal\SuperAction;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Monster\Universal\MainEntity;
use DotPlant\Store\models\goods\Goods;
use yii;

class GoodsController extends FrontendController
{
    public function actions()
    {
        return [
            'index' => [
                'class' => yii\web\ViewAction::class,
                'viewPrefix' => '',
            ],
            'show' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => FillEntities::class,
                        'entitiesMapping' => [
                            BaseStructure::class => 'page',
                            Goods::class => 'goods',
                        ],
                    ],
                    [
                        'class' => MainEntity::class,
                        'mainEntityKey' => 'goods',
                        'defaultTemplateKey' => 'goodsTemplate',
                    ],
                ],
            ],
        ];
    }
}

