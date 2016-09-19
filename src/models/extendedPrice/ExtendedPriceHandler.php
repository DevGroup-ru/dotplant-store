<?php

namespace DotPlant\Store\models\extendedPrice;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_extended_price_handlers}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $handler_class
 * @property string $target_class
 */
class ExtendedPriceHandler extends \yii\db\ActiveRecord
{
    use TagDependencyTrait;
    use EntityTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'CacheableActiveRecord' => [
                'class' => CacheableActiveRecord::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_extended_price_handlers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handler_class'], 'required'],
            [['target_class'], 'string'],
            [['name', 'handler_class'], 'string', 'max' => 255],
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
            'handler_class' => Yii::t('dotplant.store', 'Handler Class'),
            'target_class' => Yii::t('dotplant.store', 'Target Class'),
        ];
    }
}
