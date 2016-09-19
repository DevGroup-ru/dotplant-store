<?php

namespace DotPlant\Store\models\extendedPrice;

use arogachev\sortable\behaviors\numerical\ContinuousNumericalSortableBehavior;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%dotplant_store_extended_price}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $mode
 * @property integer $is_final
 * @property integer $priority
 * @property string $value
 * @property string $currency_iso_code
 * @property string $min_product_price
 * @property string $start_time
 * @property string $end_time
 * @property integer $context_id
 * @property string $calculator_type
 * @property string $target_class
 */
class ExtendedPrice extends \yii\db\ActiveRecord
{
    const MODE_AMOUNT = '-';

    const MODE_PERCENTAGE = '%';

    const MODE_DEFINE = '=';

    const CALCULATOR_TYPE_GOODS = 'goods';

    const CALCULATOR_TYPE_ORDER = 'order';

    const TARGET_TYPE_GOODS = 'goods';

    const TARGET_TYPE_ORDER = 'order';

    const TARGET_TYPE_CART_ITEMS = 'cartItems';

    const TARGET_TYPE_DELIVERY = 'delivery';


    use TagDependencyTrait;
    use EntityTrait;


    public static function getModeList()
    {
        return [
            self::MODE_AMOUNT => Yii::t('dotplant.store', 'Amount'),
            self::MODE_PERCENTAGE => Yii::t('dotplant.store', 'Percentage'),
            self::MODE_DEFINE => Yii::t('dotplant.store', 'Define'),
        ];
    }


    public static function getCalculatorTypes()
    {
        return [
            self::CALCULATOR_TYPE_GOODS => Yii::t('dotplant.store', 'Goods'),
            self::CALCULATOR_TYPE_ORDER => Yii::t('dotplant.store', 'Order'),
        ];
    }

    public static function getTargetTypes()
    {
        return [
            self::TARGET_TYPE_GOODS => Yii::t('dotplant.store', 'Goods'),
            self::TARGET_TYPE_ORDER => Yii::t('dotplant.store', 'Order'),
            self::TARGET_TYPE_CART_ITEMS => Yii::t('dotplant.store', 'Cart Items'),
            self::TARGET_TYPE_DELIVERY => Yii::t('dotplant.store', 'Delivery'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_extended_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['mode', 'calculator_type', 'target_class'], 'string'],
            [['is_final', 'priority', 'context_id'], 'integer'],
            [['value', 'min_product_price'], 'number'],
            [['start_time', 'end_time'], 'safe'],
            [['name', 'currency_iso_code'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'CacheableActiveRecord' => [
                'class' => CacheableActiveRecord::class,
            ],
            'ContinuousNumericalSortableBehavior' => [
                'class' => ContinuousNumericalSortableBehavior::class,
                'sortAttribute' => 'priority'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'name' => Yii::t('dotplant.store', 'Name'),
            'mode' => Yii::t('dotplant.store', 'Mode'),
            'is_final' => Yii::t('dotplant.store', 'Is Final'),
            'priority' => Yii::t('dotplant.store', 'Priority'),
            'value' => Yii::t('dotplant.store', 'Value'),
            'currency_iso_code' => Yii::t('dotplant.store', 'Currency Iso Code'),
            'min_product_price' => Yii::t('dotplant.store', 'Min Product Price'),
            'start_time' => Yii::t('dotplant.store', 'Start Time'),
            'end_time' => Yii::t('dotplant.store', 'End Time'),
            'context_id' => Yii::t('dotplant.store', 'Context ID'),
            'calculator_type' => Yii::t('dotplant.store', 'Calculator Type'),
            'target_class' => Yii::t('dotplant.store', 'Target Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExtendedPriceRules()
    {
        return $this->hasMany(ExtendedPriceRule::class, ['extended_price_id' => 'id'])->orderBy(['priority' => SORT_ASC]);
    }

    /**
     * Finds models
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query ActiveQuery */

        $dataProvider = new ActiveDataProvider([
            'query' => $query = static::find(),
            'pagination' => [
                //TODO configure it
                'pageSize' => 15
            ],
        ]);

        return $dataProvider;
    }
}
