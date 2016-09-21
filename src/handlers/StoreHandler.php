<?php

namespace DotPlant\Store\handlers;

use DevGroup\Users\models\User;
use DotPlant\Emails\helpers\EmailHelper;
use DotPlant\Store\events\AfterUserRegisteredEvent;
use DotPlant\Store\events\RetailCheckEvent;

/**
 * Class StoreHandler
 * @package DotPlant\Store\handlers
 */
class StoreHandler
{
    /**
     * You should add `isRetail` to event params via json editor. It is a boolean value
     * @param RetailCheckEvent $event
     */
    public static function dummyRetailCheck(RetailCheckEvent $event)
    {
        if (isset($event->data['isRetail'])) {
            $event->isRetail = $event->data['isRetail'];
        }
    }

    /**
     * You should set `emailTemplateId` via params. It is a template id from Template model of `dotplant/email` extension
     * @param AfterUserRegisteredEvent $event
     */
    public static function sendUserEmail(AfterUserRegisteredEvent $event)
    {
        $user = User::findOne($event->userId);
        if ($user !== null && isset($event->data['emailTemplateId'])) {
            EmailHelper::sendNewMessage(
                $user->email,
                $event->data['emailTemplateId'],
                [
                    'languageId' => $event->languageId,
                    'password' => $event->password,
                    'userId' => $event->userId,
                ]
            );
        }
    }
}
