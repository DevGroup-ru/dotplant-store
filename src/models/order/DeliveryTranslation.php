<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_delivery_translation}}".
 *
 * @property integer $model_id
 * @property integer $language_id
 * @property string $name
 * @property string $description
 */
class DeliveryTranslation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_delivery_translation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'language_id', 'name'], 'required'],
            [['model_id', 'language_id'], 'integer'],
            [['description'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'model_id' => Yii::t('dotplant.store', 'Model ID'),
            'language_id' => Yii::t('dotplant.store', 'Language ID'),
            'name' => Yii::t('dotplant.store', 'Name'),
            'description' => Yii::t('dotplant.store', 'Description'),
        ];
    }
}
