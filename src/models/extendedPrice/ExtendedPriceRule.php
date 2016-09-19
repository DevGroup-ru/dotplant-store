<?php

namespace DotPlant\Store\models\extendedPrice;

use arogachev\sortable\behaviors\numerical\ContinuousNumericalSortableBehavior;
use DevGroup\DataStructure\behaviors\PackedJsonAttributes;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\Store\helpers\ExtendedPriceHelper;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_extended_price_rule}}".
 *
 * @property integer $id
 * @property integer $extended_price_id
 * @property integer $extended_price_handler_id
 * @property string $operand
 * @property integer $priority
 * @property string $packed_json_params
 * @property ExtendedPriceHelper $extendedPriceHandler
 * @property ExtendedPrice $extendedPrice
 */
class ExtendedPriceRule extends \yii\db\ActiveRecord
{
    use TagDependencyTrait;
    use EntityTrait;


    public $formName = null;

    public static function getOperandList()
    {
        return [
            'OR' => Yii::t('dotplant.store', 'OR'),
            'AND' => Yii::t('dotplant.store', 'AND'),
        ];
    }

    public function formName()
    {
        return $this->formName === null ? parent::formName() : $this->formName;
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
            'PackedJsonAttributes' => [
                'class' => PackedJsonAttributes::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_extended_price_rule}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['extended_price_id', 'extended_price_handler_id'], 'required'],
            [['extended_price_id', 'extended_price_handler_id', 'priority'], 'integer'],
            [['operand', 'packed_json_params'], 'string'],
            [['params'], 'safe'],
            [
                ['extended_price_handler_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ExtendedPriceHandler::class,
                'targetAttribute' => ['extended_price_handler_id' => 'id']
            ],
            [
                ['extended_price_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ExtendedPrice::class,
                'targetAttribute' => ['extended_price_id' => 'id']
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
            'extended_price_id' => Yii::t('dotplant.store', 'Extended Price ID'),
            'extended_price_handler_id' => Yii::t('dotplant.store', 'Extended Price Handler ID'),
            'operand' => Yii::t('dotplant.store', 'Operand'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExtendedPrice()
    {
        return $this->hasOne(ExtendedPrice::class, ['id' => 'extended_price_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExtendedPriceHandler()
    {
        return $this->hasOne(ExtendedPriceHandler::class, ['id' => 'extended_price_handler_id']);
    }
}
