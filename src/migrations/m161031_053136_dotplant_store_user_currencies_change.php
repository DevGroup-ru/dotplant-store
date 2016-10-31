<?php

use DevGroup\EventsSystem\models\Event;
use DevGroup\EventsSystem\models\EventEventHandler;
use DevGroup\EventsSystem\models\EventHandler;
use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Store\handlers\StoreHandler;
use yii\db\Migration;

class m161031_053136_dotplant_store_user_currencies_change extends Migration
{
    public function up()
    {

        $eventId = Event::find()->where(['event_class_name' => AfterUserCurrencyChangeEvent::class])->scalar();
        $handlerId = EventHandler::find()->where(['class_name' => StoreHandler::class])->scalar();

        if (empty($eventId) || empty($handlerId)) {
            echo "to continue, run migrations on Currencies module";
            return false;
        }

        $this->insert(
            EventEventHandler::tableName(),
            [
                'event_id' => $eventId,
                'event_handler_id' => $handlerId,
                'method' => 'afterUserChangeCurrencySetCart',
                'packed_json_params' => '[]',
                'is_active' => 1,
                'is_system' => 0,
                'sort_order' => 0
            ]
        );
    }

    public function down()
    {
        $this->delete(
            EventEventHandler::tableName(),
            [
                'method' => 'afterUserChangeCurrencySetCart'
            ]
        );
    }

}
