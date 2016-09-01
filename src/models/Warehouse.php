<?php

namespace DotPlant\Store\models;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%dotplant_store_warehouse}}".
 *
 * @property integer $id
 * @property integer $priority
 */
class Warehouse extends \yii\db\ActiveRecord
{
    use MultilingualTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationModelClass' => WarehouseTranslation::class,
                'translationPublishedAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['priority'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'priority' => Yii::t('dotplant.store', 'Priority'),
        ];
    }

    public function search($params)
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [],
            ]
        );
    }
}
