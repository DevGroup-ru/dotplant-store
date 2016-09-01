<?php

namespace DotPlant\Store\models\price;

use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\Store\exceptions\PriceException;
use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\goods\Goods;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dotplant_store_extended_price}}".
 *
 * @property integer $id
 * @property integer $entity_id
 * @property integer $entity_model_id
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $is_percent
 * @property string $value
 * @property integer $mode
 */
class Price extends ActiveRecord implements PriceInterface
{
    protected $_calculatorClass = null;

    private static $_priceMap = [];

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
            [['entity_id', 'entity_model_id', 'start_time', 'end_time', 'is_percent', 'mode'], 'integer'],
            [['entity_model_id'], 'required'],
            [['value'], 'number'],
            [
                ['entity_id', 'entity_model_id'],
                'unique',
                'targetAttribute' => ['entity_id', 'entity_model_id'],
                'message' => 'The combination of Entity ID and Entity Model ID has already been taken.'
            ],
            [
                ['entity_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Entity::class,
                'targetAttribute' => ['entity_id' => 'id']
            ],
            [
                ['entity_model_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BaseStructure::class,
                'targetAttribute' => ['entity_model_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function create(Goods $goods)
    {
        /** @var Price | string $priceClass */
        $priceClass = get_called_class();
        if (false === $priceClass instanceof Price) {
            throw new PriceException(
                Yii::t('dotplant.store', 'Attempting to get unknown price type')
            );
        }
        if (false === isset(self::$_priceMap[$priceClass])) {
            if (false === $goods->getIsNewRecord()) {
                $price = $priceClass::find()->where([
                    'entity_id' => 0,
                    'entity_model_id' => $goods->id
                ])->one();
                if (null === $price) {
                    $price = new $priceClass;
                }
            } else {
                $price = new DummyPrice();
            }
            self::$_priceMap[$priceClass] = $price;
        } else {
            $price = self::$_priceMap[$priceClass];
        }
        return $price;
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'entity_id' => Yii::t('dotplant.store', 'Entity'),
            'entity_model_id' => Yii::t('dotplant.store', 'Entity model'),
            'start_time' => Yii::t('dotplant.store', 'Start time'),
            'end_time' => Yii::t('dotplant.store', 'End time'),
            'is_percent' => Yii::t('dotplant.store', 'Is percent'),
            'value' => Yii::t('dotplant.store', 'Value'),
            'mode' => Yii::t('dotplant.store', 'Mode'),
        ];
    }
}
