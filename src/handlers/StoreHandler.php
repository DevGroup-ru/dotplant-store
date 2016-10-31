<?php

namespace DotPlant\Store\handlers;

use DevGroup\Users\models\User;
use DotPlant\Currencies\events\AfterUserCurrencyChangeEvent;
use DotPlant\Emails\helpers\EmailHelper;
use DotPlant\Store\components\Store;
use DotPlant\Store\events\AfterUserRegisteredEvent;
use DotPlant\Store\events\RetailCheckEvent;
use DotPlant\Store\handlers\extendedPrice\ProductRule;
use DotPlant\Store\widgets\backend\EntityExtendedPriceEdit;

/**
 * Class StoreHandler
 * @package DotPlant\Store\handlers
 */
class StoreHandler
{
    /**
     * handlerClass - Extend Price Rule
     * @param \DevGroup\AdminUtils\events\ModelEditForm $event
     */
    public static function renderExtendedPriceWidget(\DevGroup\AdminUtils\events\ModelEditForm $event)
    {
        echo EntityExtendedPriceEdit::widget(['entity'=> $event->model, 'handlerClass' => $event->data['handlerClass']]);
    }

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

    /**
     * @param AfterUserCurrencyChangeEvent $event
     */
    public static function afterUserChangeCurrencySetCart(AfterUserCurrencyChangeEvent $event)
    {
        $cart = Store::getCart(false);
        if ($cart) {
            $cart->changeCurrency($event->newUserCurrency->iso_code);
        }
    }
}
