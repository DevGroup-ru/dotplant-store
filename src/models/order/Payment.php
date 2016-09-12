<?php

namespace DotPlant\Store\models\order;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_payment}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property string $handler_class_name
 * @property string $packed_json_handler_params
 * @property integer $sort_order
 * @property integer $is_active
 */
class Payment extends \yii\db\ActiveRecord
{
    use MultilingualTrait;

    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationPublishedAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_payment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['context_id'], 'required'],
            [['context_id', 'sort_order', 'is_active'], 'integer'],
            [['packed_json_handler_params'], 'string'],
            [['handler_class_name'], 'string', 'max' => 255],
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
            'handler_class_name' => Yii::t('dotplant.store', 'Handler class name'),
            'packed_json_handler_params' => Yii::t('dotplant.store', 'Handler params'),
            'sort_order' => Yii::t('dotplant.store', 'Sort order'),
            'is_active' => Yii::t('dotplant.store', 'Is active'),
        ];
    }

    /**
     * Get list data for dropdown
     * @return string[]
     */
    public static function listData($contextId = null)
    {
        $condition = $contextId === null ? ['is_active' => 1] : ['context_id' => [0, $contextId], 'is_active' => 1];
        return static::find()
            ->select(['name', 'id'])
            ->where($condition)
            ->indexBy('id')
            ->orderBy(['sort_order' => SORT_ASC])
            ->column();
    }
}
