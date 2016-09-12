<?php

namespace DotPlant\Store\models\order;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%dotplant_store_order_status}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property string $label_class
 * @property integer $is_active
 */
class OrderStatus extends \yii\db\ActiveRecord
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
        return '{{%dotplant_store_order_status}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['context_id'], 'required'],
            [['context_id', 'is_active'], 'integer'],
            [['label_class'], 'string', 'max' => 255],
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
            'label_class' => Yii::t('dotplant.store', 'Label class'),
            'is_active' => Yii::t('dotplant.store', 'Is active'),
        ];
    }

    /**
     * Get list data for dropdown
     * @return string[]
     */
    public static function listData($contextId = null)
    {
        $condition = ['is_active' => 1];
        if ($contextId !== null) {
            $condition['context_id'] = $contextId;
        }
        return (new Query())
            ->select(['label', 'id'])
            ->from(static::tableName())
            ->innerJoin(OrderStatusTranslation::tableName(), 'id = model_id')
            ->groupBy(['model_id'])
            ->where($condition)
            ->indexBy('id')
            ->orderBy(['sort_order' => SORT_ASC, 'language_id' => SORT_ASC])
            ->column();
    }
}
