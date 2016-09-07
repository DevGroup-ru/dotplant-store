<?php

namespace DotPlant\Store\models\order;

use DotPlant\Currencies\CurrenciesModule;
use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\warehouse\Warehouse;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_item}}".
 *
 * @property integer $id
 * @property integer $cart_id
 * @property integer $order_id
 * @property integer $goods_id
 * @property integer $warehouse_id
 * @property double $quantity
 * @property string $total_price_with_discount
 * @property string $total_price_without_discount
 * @property string $seller_price
 */
class OrderItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id'], 'required'],
            [['cart_id', 'order_id', 'goods_id', 'warehouse_id'], 'integer'],
            [['quantity', 'total_price_with_discount', 'total_price_without_discount', 'seller_price'], 'number'],
            [['warehouse_id'], 'exist', 'skipOnError' => true, 'targetClass' => Warehouse::className(), 'targetAttribute' => ['warehouse_id' => 'id']],
            [['cart_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cart::className(), 'targetAttribute' => ['cart_id' => 'id']],
            [['goods_id'], 'exist', 'skipOnError' => true, 'targetClass' => Goods::className(), 'targetAttribute' => ['goods_id' => 'id']],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['warehouse_id'], 'validateWarehouse'],
        ];
    }

    public function validateWarehouse()
    {
        if (!empty($this->order_id) && empty($this->warehouse_id)) {
            $this->addError('warehouse_id', Yii::t('yii', '{attribute} cannot be blank.'));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'cart_id' => Yii::t('dotplant.store', 'Cart'),
            'order_id' => Yii::t('dotplant.store', 'Order'),
            'goods_id' => Yii::t('dotplant.store', 'Goods'),
            'warehouse_id' => Yii::t('dotplant.store', 'Warehouse'),
            'quantity' => Yii::t('dotplant.store', 'Quantity'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total price with discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total price without discount'),
            'seller_price' => Yii::t('dotplant.store', 'Seller price'),
        ];
    }

    public function calculate()
    {
        $goods = $this->findGoods($this->goods_id);
        $warehouses = Warehouse::getWarehouses($this->goods_id);
        if (!empty($this->warehouse_id)) {
            if (!isset($warehouses[$this->warehouse_id])) {
                throw new OrderException(Yii::t('dotplant.store', 'The warehouse is not available'));
            }
            if ($warehouses[$this->warehouse_id]['available_count'] < $this->quantity) {
                throw new OrderException(Yii::t('dotplant.store', 'The warehouse has no enough goods'));
            }
        } else {
            /**
             * @todo: There will be a autoselecting of warehouse by priority or another logic
             * Now we just check that one of warehouses has enough items
             */
            $hasEnough = false;
            foreach ($warehouses as $warehouseId => $warehouse) {
                if ($warehouse['available_count'] >= $this->quantity) {
                    $hasEnough = true;
                    break;
                }
            }
            if (!$hasEnough) {
                throw new OrderException(Yii::t('dotplant.store', 'The warehouse has no enough goods'));
            }
        }
        // @todo: Add a check warehouse count
        // @todo: Calculate price and discount. Dummy calculation below
        $price = $goods->getPrice($this->warehouse_id);
        $this->total_price_with_discount = CurrencyHelper::convertCurrencies(
            $price['value'],
            CurrencyHelper::findCurrencyByIso($price['iso_code']),
            CurrencyHelper::getUserCurrency()
        ) * $this->quantity;
        $this->total_price_without_discount = CurrencyHelper::convertCurrencies(
            isset($price['original_value']) ? $price['original_value'] : $price['value'],
            CurrencyHelper::findCurrencyByIso($price['iso_code']),
            CurrencyHelper::getUserCurrency()
        ) * $this->quantity;
    }

    protected function findGoods($id)
    {
        $model = Goods::get($id);
        if ($model === null) {
            throw new OrderException(Yii::t('dotplant.store', 'Goods not found'));
        }
        return $model;
    }
}
