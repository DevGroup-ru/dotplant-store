<?php

namespace DotPlant\Store\models\order;

use DevGroup\Entity\traits\BaseActionsInfoTrait;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SoftDeleteTrait;
use DotPlant\Store\components\RelationQueryByLanguage;
use DotPlant\Store\components\SortByContextLanguageExpression;
use DotPlant\Store\events\AfterOrderManagerChangeEvent;
use DotPlant\Store\events\AfterOrderStatusChangeEvent;
use DotPlant\Store\events\OrderEvent;
use DotPlant\Store\Module;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%dotplant_store_order}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property integer $status_id
 * @property integer $delivery_id
 * @property integer $payment_id
 * @property string $currency_iso_code
 * @property double $items_count
 * @property string $total_price_with_discount
 * @property string $total_price_without_discount
 * @property integer $is_retail
 * @property integer $manager_id
 * @property integer $promocode_id
 * @property string $promocode_discount
 * @property string $promocode_name
 * @property double $rate_to_main_currency
 * @property integer $created_by
 * @property integer $created_at
 * @property integer $updated_by
 * @property integer $updated_at
 * @property integer $forming_time
 * @property string $hash
 * @property boolean $is_deleted
 *
 * @property OrderDeliveryInformation $deliveryInformation
 * @property OrderStatus $status
 * @property Delivery $delivery
 * @property Payment $payment
 * @property OrderItem[] $items
 * @property Cart $cart
 */
class Order extends \yii\db\ActiveRecord
{
    use EntityTrait;
    use BaseActionsInfoTrait;
    use SoftDeleteTrait;

    private $_cart = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order}}';
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return [
            [['context_id', 'status_id', 'currency_iso_code'], 'required'],
            [['manager_id'], 'required', 'on' => 'backend-order-updating'],
            [
                [
                    'context_id',
                    'status_id',
                    'delivery_id',
                    'payment_id',
                    'is_retail',
                    'manager_id',
                    'promocode_id',
                    'created_by',
                    'created_at',
                    'updated_by',
                    'updated_at',
                    'forming_time',
                    'is_deleted',
                    'user_id',
                ],
                'integer'
            ],
            [
                [
                    'items_count',
                    'total_price_with_discount',
                    'total_price_without_discount',
                    'promocode_discount',
                    'rate_to_main_currency'
                ],
                'number'
            ],
            [['currency_iso_code'], 'string', 'max' => 3],
            [['promocode_name'], 'string', 'max' => 255],
            [['hash'], 'string', 'max' => 32],
            [['hash'], 'unique'],
            [
                ['payment_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Payment::className(),
                'targetAttribute' => ['payment_id' => 'id']
            ],
            [
                ['delivery_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Delivery::className(),
                'targetAttribute' => ['delivery_id' => 'id']
            ],
            [
                ['status_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => OrderStatus::className(),
                'targetAttribute' => ['status_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $baseActionsInfoAttributes = ['created_by', 'created_at', 'updated_by', 'updated_at', 'user_id'];
        $notBaseAttributes = [
            'order-creation' => [
                    'context_id',
                    'currency_iso_code',
                    'status_id',
                    'is_retail',
                    'items_count',
                    'total_price_with_discount',
                    'total_price_without_discount',
                    'rate_to_main_currency',
                ],
            'single-step-order' => ['payment_id', 'delivery_id'],
            'status-changing' => ['status_id'],
            // backend
            'backend-order-updating' => [
                    'status_id',
                    'delivery_id',
                    'payment_id',
                    'manager_id',
                ],
            'backend-order-soft-deleting' => ['is_deleted'],
            'attach-manager' => ['manager_id'],
        ];
        return array_map(
            function ($item) use ($baseActionsInfoAttributes) {
                return array_merge($item, $baseActionsInfoAttributes);
            },
            $notBaseAttributes
        );
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'context_id' => Yii::t('dotplant.store', 'Context'),
            'status_id' => Yii::t('dotplant.store', 'Status'),
            'delivery_id' => Yii::t('dotplant.store', 'Delivery'),
            'payment_id' => Yii::t('dotplant.store', 'Payment'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency iso code'),
            'items_count' => Yii::t('dotplant.store', 'Items count'),
            'total_price_with_discount' => Yii::t('dotplant.store', 'Total price with discount'),
            'total_price_without_discount' => Yii::t('dotplant.store', 'Total price without discount'),
            'is_retail' => Yii::t('dotplant.store', 'Is retail'),
            'manager_id' => Yii::t('dotplant.store', 'Manager'),
            'promocode_id' => Yii::t('dotplant.store', 'Promocode'),
            'promocode_discount' => Yii::t('dotplant.store', 'Promocode discount'),
            'promocode_name' => Yii::t('dotplant.store', 'Promocode name'),
            'rate_to_main_currency' => Yii::t('dotplant.store', 'Rate to main currency'),
            'created_by' => Yii::t('dotplant.store', 'Created by'),
            'created_at' => Yii::t('dotplant.store', 'Created at'),
            'updated_by' => Yii::t('dotplant.store', 'Updated by'),
            'updated_at' => Yii::t('dotplant.store', 'Updated at'),
            'forming_time' => Yii::t('dotplant.store', 'Forming time'),
            'hash' => Yii::t('dotplant.store', 'Hash'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeliveryInformation()
    {
        return $this->hasOne(OrderDeliveryInformation::class, ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->createRelation(OrderStatus::class, ['id' => $this->status_id], ['id' => 'status_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDelivery()
    {
        return $this->createRelation(Delivery::class, ['id' => $this->delivery_id], ['id' => 'delivery_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayment()
    {
        return $this->createRelation(Payment::class, ['id' => $this->payment_id], ['id' => 'payment_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])->indexBy('id');
    }

    /**
     * Attach a manager to the order
     * @param int $userId
     */
    public function attachManager($userId)
    {
        $this->manager_id = $userId;
        $this->scenario = 'attach-manager';
        return $this->save();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->hash = md5(uniqid() . time());
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['status_id']) && $changedAttributes['status_id'] != $this->status_id) {
            Module::module()->trigger(
                Module::EVENT_AFTER_ORDER_STATUS_CHANGE,
                new AfterOrderStatusChangeEvent(
                    [
                        'orderId' => $this->id,
                        'statusId' => $this->status_id,
                        'oldStatusId' => $changedAttributes['status_id'],
                    ]
                )
            );
        }
        if (
            array_key_exists('manager_id', $changedAttributes) &&
            ($changedAttributes['manager_id'] != $this->manager_id)
        ) {
            Module::module()->trigger(
                Module::EVENT_AFTER_ORDER_MANAGER_CHANGE,
                new AfterOrderManagerChangeEvent(
                    [
                        'orderId' => $this->id,
                        'managerId' => $this->manager_id,
                        'oldManagerId' => $changedAttributes['manager_id'],
                    ]
                )
            );
        }
    }

    /**
     * Get cart via OrderItem
     * @return Cart|null
     */
    public function getCart()
    {
        if ($this->_cart === false) {
            $items = $this->items;
            $first = reset($items);
            $this->_cart = $first !== null ? $first->cart : null;
        }
        return $this->_cart;
    }

    /**
     * Create a smart relation with translations
     * @param string $className
     * @param mixed $whereCondition
     * @param mixed $relationCondition
     * @return ActiveQuery
     */
    private function createRelation($className, $whereCondition, $relationCondition)
    {
//        if ($this->context_id != Yii::$app->multilingual->context_id) {
            $query = (new ActiveQuery($className))
                ->where($whereCondition)
                ->joinWith(['translations'])
                ->orderBy([new SortByContextLanguageExpression($this->context_id)]);
            $query->multiple = false;
            return $query;
//        } else {
//            return $this->hasOne($className, $relationCondition);
//        }
    }
}
