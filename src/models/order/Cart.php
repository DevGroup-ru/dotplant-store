<?php

namespace DotPlant\Store\models\order;

use DevGroup\Entity\traits\BaseActionsInfoTrait;
use DevGroup\Entity\traits\EntityTrait;
use DotPlant\Store\exceptions\OrderException;
use DotPlant\Store\models\warehouse\Warehouse;
use DotPlant\Store\Module;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dotplant_store_cart}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property integer $is_locked
 * @property integer $is_retail
 * @property string $currency_iso_code
 * @property double $items_count
 * @property double $total_price_with_discount
 * @property double $total_price_without_discount
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property OrderItem[] $items
 */
class Cart extends ActiveRecord
{
    use EntityTrait;
    use BaseActionsInfoTrait;

    protected $blameableAttributes = [
        ActiveRecord::EVENT_BEFORE_INSERT => ['created_by'],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_cart}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['context_id', 'currency_iso_code'], 'required'],
            [['context_id', 'is_locked', 'is_retail', 'created_by', 'created_at', 'updated_at'], 'integer'],
            [['items_count', 'total_price_with_discount', 'total_price_without_discount'], 'number'],
            [['currency_iso_code'], 'string', 'max' => 3],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'context_id' => Yii::t('dotplant.store', 'Context'),
            'is_locked' => Yii::t('dotplant.store', 'Is locked'),
            'is_retail' => Yii::t('dotplant.store', 'Is retail'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency iso code'),
            'items_count' => Yii::t('dotplant.store', 'Items count'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total price with discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total price without discount'),
            'created_by' => Yii::t('dotplant.store', 'Created by'),
            'created_at' => Yii::t('dotplant.store', 'Created at'),
            'updated_at' => Yii::t('dotplant.store', 'Updated at'),
        ];
    }

    public function addItem($goodsId, $quantity, $warehouseId)
    {
        $this->checkLock();
        if ($quantity <= 0) {
            throw new OrderException(Yii::t('dotplant.store', 'The quantity must be more than zero'));
        }
        if (empty($warehouseId)) {
            $warehouseId = Warehouse::getOptimalWarehouse($goodsId, $quantity)->warehouse_id;
        }
        $item = Module::module()->allowToAddSameGoods == 0
            ? $this->findItem(
                [
                    'cart_id' => $this->id,
                    'goods_id' => $goodsId,
                    'warehouse_id' => $warehouseId,
                ],
                false
            )
            : null;
        if ($item === null) {
            $item = new OrderItem;
            $item->loadDefaultValues();
            $item->cart_id = $this->id;
            $item->goods_id = $goodsId;
            $item->warehouse_id = $warehouseId;
        }
        $item->quantity += $quantity;
        $item->calculate();
        if (!$item->save()) {
            throw new OrderException(Yii::t('dotplant.store', 'Can not add a goods to cart'));
        }
        $this->calculate();
    }

    public function changeItemQuantity($id, $quantity)
    {
        $this->checkLock();
        $item = $this->findItem(['id' => $id, 'cart_id' => $this->id]);
        if ($quantity > 0) {
            $item->quantity = $quantity;
            $item->calculate();
            if (!$item->save()) {
                throw new OrderException(Yii::t('dotplant.store', 'Can not change a goods quantity'));
            }
        } else {
            $item->delete();
        }
        $this->calculate();
    }

    public function removeItem($id)
    {
        $this->checkLock();
        $item = $this->findItem(['id' => $id, 'cart_id' => $this->id]);
        $item->delete();
        $this->calculate();
    }

    public function clear()
    {
        $this->checkLock();
        OrderItem::deleteAll(
            [
                'cart_id' => $this->id,
            ]
        );
        $this->attributes = [
            'total_price_with_discount' => 0,
            'total_price_without_discount' => 0,
            'items_count' => 0,
        ];
        $this->save();
    }

    public function calculate() // @todo: public or private?
    {
        $this->items_count = 0;
        $this->total_price_with_discount = 0;
        $this->total_price_without_discount = 0;
        foreach ($this->items as $item) {
            $this->total_price_with_discount += $item->total_price_with_discount;
            $this->total_price_without_discount += $item->total_price_without_discount;
            if ($item->goods_id == 0) {
                continue; // It's a delivery. Do not count it as an order item
            }
            $this->items_count += Module::module()->countUniqueItemsOnly == 1 ? 1 : $item->quantity;
        }
        // @todo: Add another calculation (discounts, etc)
    }

    /**
     * Whether to allow to edit the cart
     * @return bool
     */
    public function canEdit()
    {
        return $this->is_locked == 0;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['cart_id' => 'id']);
    }

    public function prepare()
    {
        foreach ($this->items as $item) {
            if (!empty($item->warehouse_id)) {
                continue;
            }
            $item->warehouse_id = Warehouse::getOptimalWarehouse($item->goods_id, $item->quantity)->warehouse_id;
            if (!$item->save(true, ['warehouse_id'])) {
                var_dump($item->errors);
                die;
            }
        }
    }

    public function reserve()
    {
        foreach ($this->items as $item) {
            Warehouse::getWarehouse($item->goods_id, $item->warehouse_id, false)->reserve($item->quantity);
        }
    }

    /**
     * @throws OrderException
     */
    protected function checkLock()
    {
        if (!$this->canEdit()) {
            throw new OrderException(
                Yii::t('dotplant.store', 'Cart is locked. Cancel the ordering process to unlock it')
            );
        }
    }

    /**
     * @param mixed $condition
     * @param bool $throwException
     * @return OrderItem
     * @throws OrderException
     */
    protected function findItem($condition, $throwException = true)
    {
        $model = OrderItem::findOne($condition);
        if ($model === null && $throwException) {
            throw new OrderException(Yii::t('dotplant.store', 'Item not found'));
        }
        return $model;
    }
}
