<?php


namespace DotPlant\Store\components\calculator;


use DotPlant\Store\interfaces\NoGoodsCalculatorInterface;
use DotPlant\Store\models\order\Delivery;
use DotPlant\Store\models\order\OrderItem;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

class OrderItemDeliveryCalculator implements NoGoodsCalculatorInterface
{

    /**
     * Calculates object price
     *
     * @param OrderItem $orderDeliveryItem
     *
     * @return array
     */
    public static function getPrice($orderDeliveryItem)
    {
        if ($orderDeliveryItem instanceof OrderItem === false) {
            throw new InvalidParamException;
        }
        $price = ['totalPriceWithoutDiscount' => 0, 'totalPriceWithDiscount' => 0, 'items' => 0, 'extendedPrice' => []];

        $handlerParams = ArrayHelper::getValue($orderDeliveryItem->params, 'deliveryHandlerParams', []);
        if (empty($handlerParams)) {
            throw new InvalidParamException;
        }

        $deliveryId = ArrayHelper::remove($handlerParams, 'deliveryId', 0);
        $delivery = Delivery::findOne($deliveryId);

        $handler = \Yii::createObject($delivery->handler_class_name, $handlerParams);
        $price['totalPriceWithoutDiscount'] = $handler->calculate();

        return $price;
    }
}