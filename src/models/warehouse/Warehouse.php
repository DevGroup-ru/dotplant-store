<?php

namespace DotPlant\Store\models\warehouse;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "{{%dotplant_store_warehouse}}".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $priority
 */
class Warehouse extends \yii\db\ActiveRecord
{
    use MultilingualTrait;

    const TYPE_WAREHOUSE = 1;
    const TYPE_SELLER = 2;

    public static function getTypes()
    {
        return [
            self::TYPE_WAREHOUSE => Yii::t('dotplant.store', 'Warehouse'),
            self::TYPE_SELLER => Yii::t('dotplant.store', 'Seller'),
        ];
    }

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
            [['priority', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'type' => Yii::t('dotplant.store', 'Type'),
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
        return $dataProvider;
    }
}
