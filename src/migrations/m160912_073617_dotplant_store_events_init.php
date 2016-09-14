<?php

use DevGroup\EventsSystem\models\Event;
use DevGroup\EventsSystem\models\EventHandler;
use DevGroup\EventsSystem\models\EventGroup;
use DotPlant\Store\events\OrderAfterStatusChangeEvent;
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
                [$egId, 'Order after status change', OrderAfterStatusChangeEvent::class, 'dotplant.store.orderAfterStatusChange'],
                [$egId, 'Retail check', RetailCheckEvent::class, 'dotplant.store.retailCheck'],
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
            ['event_class_name' => [RetailCheckEvent::class, OrderAfterStatusChangeEvent::class]]
        );
        $this->delete(EventGroup::tableName(), ['owner_class_name' => Module::class]);
    }
}
