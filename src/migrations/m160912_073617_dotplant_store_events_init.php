<?php

use DevGroup\EventsSystem\models\Event;
use DevGroup\EventsSystem\models\EventHandler;
use DevGroup\EventsSystem\models\EventGroup;
use DotPlant\Store\events\AfterOrderManagerChangeEvent;
use DotPlant\Store\events\AfterOrderStatusChangeEvent;
use DotPlant\Store\events\AfterUserRegisteredEvent;
use DotPlant\Store\events\RetailCheckEvent;
use DotPlant\Store\handlers\OrderHandler;
use DotPlant\Store\handlers\StoreHandler;
use DotPlant\Store\Module;
use yii\db\Migration;

class m160912_073617_dotplant_store_events_init extends Migration
{
    public function up()
    {
        $this->insert(
            EventGroup::tableName(),
            [
                'name' => 'Store',
                'owner_class_name' => Module::class,
            ]
        );
        $egId = $this->db->lastInsertID;
        $this->batchInsert(
            Event::tableName(),
            ['event_group_id', 'name', 'event_class_name', 'execution_point'],
            [
                [$egId, 'After order status change', AfterOrderStatusChangeEvent::class, Module::EVENT_AFTER_ORDER_STATUS_CHANGE],
                [$egId, 'After order manager change', AfterOrderManagerChangeEvent::class, Module::EVENT_AFTER_ORDER_MANAGER_CHANGE],
                [$egId, 'Retail check', RetailCheckEvent::class, Module::EVENT_RETAIL_CHECK],
                [$egId, 'After user registered', AfterUserRegisteredEvent::class, Module::EVENT_AFTER_USER_REGISTERED],
            ]
        );
        $this->batchInsert(
            EventHandler::tableName(),
            ['name', 'class_name'],
            [
                ['Store handler', StoreHandler::class],
                ['Order handler', OrderHandler::class],
            ]
        );
    }

    public function down()
    {
        $this->delete(
            EventHandler::tableName(),
            ['class_name' => [StoreHandler::class, OrderHandler::class]]
        );
        $this->delete(
            Event::tableName(),
            [
                'event_class_name' => [
                    RetailCheckEvent::class,
                    AfterOrderStatusChangeEvent::class,
                    AfterOrderManagerChangeEvent::class,
                    AfterUserRegisteredEvent::class
                ]
            ]
        );
        $this->delete(EventGroup::tableName(), ['owner_class_name' => Module::class]);
    }
}
