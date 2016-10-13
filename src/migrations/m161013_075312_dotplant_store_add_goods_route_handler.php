<?php

use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\handlers\GoodsRouteHandler;
use DotPlant\Store\models\goods\GoodsCategory;
use yii\db\Migration;
use yii\helpers\ArrayHelper;

class m161013_075312_dotplant_store_add_goods_route_handler extends Migration
{
    public function up()
    {
        $entity = Entity::findOne(['class_name' => GoodsCategory::class]);
        if ($entity !== null) {
            $entity->route_handlers = ArrayHelper::merge(
                $entity->route_handlers,
                [
                    [
                        'class' => GoodsRouteHandler::class,
                    ]
                ]
            );
            $entity->save(false);
        }
    }

    public function down()
    {
        $entity = Entity::findOne(['class_name' => GoodsCategory::class]);
        if ($entity !== null) {
            $routeHandlers = $entity->route_handlers;
            foreach ($routeHandlers as $index => $routeHandler) {
                if ($routeHandler['class'] == GoodsRouteHandler::class) {
                    unset($routeHandlers[$index]);
                }
            }
            $entity->route_handlers = $routeHandlers;
            $entity->save(false);
        }
    }
}
