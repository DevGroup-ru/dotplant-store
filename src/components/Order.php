<?php

namespace DotPlant\Store\components;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\order\Cart;
use Yii;

/**
 * Class Order
 *
 * @package DotPlant\Store\components
 */
class Order
{
    const CART_SESSION_KEY = 'DotPlant:Store:CartId';

    protected static function createCart()
    {
        $model = new Cart();
        $model->loadDefaultValues();
        $model->context_id = Yii::$app->multilingual->context_id;
        $model->created_by = Yii::$app->user->id;
        $model->currency_iso_code = CurrencyHelper::getUserCurrency()->iso_code;
        if (!$model->save()) {
            throw new OrderException(Yii::t('dotplant.store', 'Can not create a new cart'));
        }
        Yii::$app->session->set(self::CART_SESSION_KEY, $model->id);
        return $model;
    }

    /**
     * Get cart instance
     * @param bool $createIfNotExists
     * @return Cart|null
     */
    public static function getCart($createIfNotExists = true)
    {
        $id = Yii::$app->session->get(self::CART_SESSION_KEY);
        $userId = Yii::$app->user->id;
        $model = null;
        if ($userId !== null) {
            $model = Cart::findOne(
                [
                    'context_id' => Yii::$app->multilingual->context_id,
                    'created_by' => $userId,
                ]
            );
        }
        if ($model === null && $id !== null) {
            $model = Cart::findOne(
                [
                    'id' => $id,
                    'context_id' => Yii::$app->multilingual->context_id,
                ]
            );
        }
        if ($model === null && $createIfNotExists) {
            $model = static::createCart();
        }
        return $model;
    }
}
