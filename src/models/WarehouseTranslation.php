<?php

namespace DotPlant\Store\models;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_warehouse_translation}}".
 *
 * @property integer $model_id
 * @property integer $language_id
 * @property string $name
 * @property string $address
 */
class WarehouseTranslation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_warehouse_translation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language_id', 'name'], 'required'],
            [['model_id', 'language_id'], 'integer'],
            [['address'], 'string'],
            [['name'], 'string', 'max' => 255],
            [
                ['model_id'],
                'exist',
                'targetClass' => Warehouse::class,
                'targetAttribute' => ['model_id' => 'id'],
            ],
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
            'address' => Yii::t('dotplant.store', 'Address'),
        ];
    }
}
