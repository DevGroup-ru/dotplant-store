<?php

namespace DotPlant\Store\models\filters;

use DevGroup\DataStructure\models\StaticValue;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dotplant_store_filter_static_value}}".
 *
 * @property integer $id
 * @property integer $static_value_id
 * @property integer $sort_order
 * @property integer $display
 * @property integer $filter_set_id
 *
 * @property FilterSetsModel $filterSet
 * @property StaticValue $staticValue
 */
class FilterStaticValueModel extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_filter_static_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['static_value_id', 'sort_order', 'display', 'filter_set_id'], 'required'],
            [['static_value_id', 'sort_order', 'display', 'filter_set_id'], 'integer'],
            [
                ['filter_set_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => FilterSetsModel::className(),
                'targetAttribute' => ['filter_set_id' => 'id'],
            ],
            [
                ['static_value_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StaticValue::className(),
                'targetAttribute' => ['static_value_id' => 'id'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'static_value_id' => Yii::t('app', 'Static Value ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'display' => Yii::t('app', 'Display'),
            'filter_set_id' => Yii::t('app', 'Filter Set ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilterSet()
    {
        return $this->hasOne(FilterSetsModel::className(), ['id' => 'filter_set_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaticValue()
    {
        return $this->hasOne(StaticValue::className(), ['id' => 'static_value_id']);
    }


}
