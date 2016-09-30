<?php

namespace DotPlant\Store\handlers;

use DotPlant\Store\interfaces\DeliveryInterface;
use yii\base\Component;

class SelfPickedDelivery extends Component implements DeliveryInterface
{
    public function calculate()
    {
        return 0;
    }
}
