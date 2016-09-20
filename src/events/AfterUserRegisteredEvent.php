<?php

namespace DotPlant\Store\events;

class AfterUserRegisteredEvent extends StoreEvent
{
    public $languageId;
    public $password;
    public $userId;
}
