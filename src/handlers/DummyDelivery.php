<?php


namespace DotPlant\Store\handlers;


use yii\base\Component;

class DummyDelivery extends Component
{

    public function calculate()
    {
        return 0;
    }
}