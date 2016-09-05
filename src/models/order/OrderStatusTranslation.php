<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_order_status_translation}}".
 *
 * @property integer $model_id
 * @property integer $language_id
 * @property string $name
 * @property string $label
 * @property string $description
 */
class OrderStatusTranslation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_status_translation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['model_id', 'language_id', 'label'], 'required'],
            [['model_id', 'language_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'label'], 'string', 'max' => 255],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => DotplantStoreOrderStatus::className(), 'targetAttribute' => ['model_id' => 'id']],
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
            'label' => Yii::t('dotplant.store', 'Label'),
            'description' => Yii::t('dotplant.store', 'Description'),
        ];
    }
}
