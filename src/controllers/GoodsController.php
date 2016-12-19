<?php

namespace DotPlant\Store\controllers;

use DevGroup\Frontend\controllers\FrontendController;
use DevGroup\Frontend\Universal\Core\FillEntities;
use DevGroup\Frontend\Universal\SuperAction;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Monster\Universal\MainEntity;
use DotPlant\Store\models\goods\Goods;
use yii;
use DotPlant\Monster\Universal\ServiceMonsterAction;
use DotPlant\Monster\models\ServiceEntity;
use DotPlant\Store\providers\GoodsSearchProvider;

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
            'search' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => ServiceMonsterAction::class,
                        'serviceTemplateKey' => 'search',
                        'serviceEntityCallback' => function (ServiceEntity $entity) {
                            $entity->providers[] = GoodsSearchProvider::class;
                        },
                    ],
                ],
            ]
        ];
    }
}

