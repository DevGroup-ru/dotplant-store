<?php

use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\handlers\FilterRouteHandler;
use DotPlant\Store\models\goods\GoodsCategory;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Json;

class m161214_113844_dotplant_store_add_filter_route_handler extends Migration
{
    public function up()
    {
        $handlers = Json::decode(
            (new Query)
                ->select(['packed_json_route_handlers'])
                ->from(Entity::tableName())
                ->where(
                    [
                        'class_name' => GoodsCategory::class,
                    ]
                )
                ->scalar()
        );
        $handlers[] = [
            'class' => FilterRouteHandler::class,
        ];
        $this->update(
            Entity::tableName(),
            ['packed_json_route_handlers' => Json::encode($handlers)],
            ['class_name' => GoodsCategory::class]
        );
    }

    public function down()
    {
        $handlers = Json::decode(
            (new Query)
                ->select(['packed_json_route_handlers'])
                ->from(Entity::tableName())
                ->where(
                    [
                        'class_name' => GoodsCategory::class,
                    ]
                )
                ->scalar()
        );
        foreach ($handlers as $index => $handler) {
            if ($handler['class'] == FilterRouteHandler::class) {
                unset($handlers[$index]);
            }
        }
        $this->update(
            Entity::tableName(),
            ['packed_json_route_handlers' => Json::encode($handlers)],
            ['class_name' => GoodsCategory::class]
        );
    }
}
