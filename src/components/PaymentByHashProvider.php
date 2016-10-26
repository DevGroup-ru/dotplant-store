<?php


namespace DotPlant\Store\components;


use DevGroup\Frontend\Universal\ActionData;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use DotPlant\Store\models\order\Order;
use DotPlant\Store\models\order\Payment;
use Yii;

class PaymentByHashProvider extends DataEntityProvider
{
    /**
     * @var string the region key
     */
    public $regionKey = 'paymentRegion';

    /**
     * @var string the material key
     */
    public $materialKey = 'paymentMaterial';

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
        $hash = Yii::$app->request->get('hash');
        $orderDeliveryInformation = Payment::find()->leftJoin(
            Order::tableName(),
            [Order::tableName() .'.payment_id' => Payment::tableName() . '.id']
        )->where(['hash' => $hash])->one();
        return [
            $this->regionKey => [
                $this->materialKey => [
                    'orderDeliveryInformation' => $orderDeliveryInformation,
                ],
            ],
        ];
    }
}