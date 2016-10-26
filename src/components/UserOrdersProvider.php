<?php


namespace DotPlant\Store\components;


use DevGroup\Frontend\Universal\ActionData;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use Yii;

class UserOrdersProvider extends DataEntityProvider
{
    /**
     * @var string the region key
     */
    public $regionKey = 'ordersRegion';

    /**
     * @var string the material key
     */
    public $materialKey = 'ordersMaterial';

    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }

    /**
     * @param ActionData $actionData
     *
     * @return mixed
     */
    public function getEntities(&$actionData)
    {
        $orders = Store::getOrders(Yii::$app->user->id);
        return [
            $this->regionKey => [
                $this->materialKey => [
                    "orders" => $orders,
                ],
            ],
        ];
    }
}