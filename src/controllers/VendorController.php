<?php

namespace DotPlant\Store\controllers;

use DevGroup\Frontend\controllers\FrontendController;
use DevGroup\Frontend\Universal\Core\FillEntities;
use DevGroup\Frontend\Universal\SuperAction;
use DotPlant\Monster\Universal\MainEntity;
use DotPlant\Store\models\vendor\Vendor;
use yii\web\ViewAction;

class VendorController extends FrontendController
{
    public function actions()
    {
        return [
            'index' => [
                'class' => ViewAction::class,
                'viewPrefix' => '',
            ],
            'show' => [
                'class' => SuperAction::class,
                'actions' => [
                    [
                        'class' => FillEntities::class,
                        'entitiesMapping' => [
                            Vendor::class => 'vendor',
                        ],
                    ],
                    [
                        'class' => MainEntity::class,
                        'mainEntityKey' => 'vendor',
                        'defaultTemplateKey' => 'vendorTemplate',
                    ],
                ],
            ],
        ];
    }
}
