<?php

use DevGroup\AdminUtils\events\ModelEditForm;
use DevGroup\EventsSystem\models\Event;
use DevGroup\EventsSystem\models\EventEventHandler;
use DevGroup\EventsSystem\models\EventGroup;
use DevGroup\EventsSystem\models\EventHandler;
use DotPlant\EntityStructure\actions\BaseEntityEditAction;
use DotPlant\EntityStructure\StructureModule;
use DotPlant\Store\actions\goods\GoodsManageAction;
use DotPlant\Store\handlers\extendedPrice\ProductRule;
use DotPlant\Store\handlers\extendedPrice\StructureRule;
use DotPlant\Store\handlers\StoreHandler;
use DotPlant\Store\Module;
use yii\db\Migration;
use yii\helpers\Json;

class m160927_072505_dotplant_store_events_extended_price_widget extends Migration
{
    public function up()
    {
        $eventGroupId = EventGroup::find()->where(['owner_class_name' => Module::class])->scalar();
        $eventHandlerId = EventHandler::find()->where(['class_name' => StoreHandler::class])->scalar();

        $event = new Event(
            [
                'event_group_id' => $eventGroupId,
                'name' => 'Extended Price on Product backend page',
                'event_class_name' => ModelEditForm::class,
                'execution_point' => GoodsManageAction::EVENT_AFTER_FORM
            ]
        );

        $event->save();

        $eventEventHandler = new EventEventHandler(
            [
                'event_id' => $event->id,
                'event_handler_id' => $eventHandlerId,
                'method' => 'renderExtendedPriceWidget',
                'packed_json_params' => Json::encode(['handlerClass' => ProductRule::class]),
                'is_active' => 1,
                'is_system' => 0,
                'sort_order' => 0
            ]
        );

        $eventEventHandler->save();

        $eventStructureGroupId = EventGroup::find()->where(['owner_class_name' => StructureModule::class])->scalar();
        $event = new Event(
            [
                'event_group_id' => $eventStructureGroupId,
                'name' => 'Extended Price on Structure backend page',
                'event_class_name' => ModelEditForm::class,
                'execution_point' => BaseEntityEditAction::EVENT_AFTER_FORM
            ]
        );

        $event->save();

        $eventEventHandler = new EventEventHandler(
            [
                'event_id' => $event->id,
                'event_handler_id' => $eventHandlerId,
                'method' => 'renderExtendedPriceWidget',
                'packed_json_params' => Json::encode(['handlerClass' => StructureRule::class]),
                'is_active' => 1,
                'is_system' => 0,
                'sort_order' => 0
            ]
        );

        $eventEventHandler->save();
    }

    public function down()
    {
        Event::deleteAll([
            'execution_point' => [
                GoodsManageAction::EVENT_AFTER_FORM,
                BaseEntityEditAction::EVENT_AFTER_FORM
            ]
        ]);
    }
}
