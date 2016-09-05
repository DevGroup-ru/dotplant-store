<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_delivery}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property string $handler_class_name
 * @property string $packed_json_handler_params
 * @property integer $sort_order
 * @property integer $is_active
 */
class Delivery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_delivery}}';
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
            'context_id' => Yii::t('dotplant.store', 'Context ID'),
            'handler_class_name' => Yii::t('dotplant.store', 'Handler Class Name'),
            'packed_json_handler_params' => Yii::t('dotplant.store', 'Packed Json Handler Params'),
            'sort_order' => Yii::t('dotplant.store', 'Sort Order'),
            'is_active' => Yii::t('dotplant.store', 'Is Active'),
        ];
    }
}
