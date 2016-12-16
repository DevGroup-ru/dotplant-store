<?php

namespace DotPlant\Store\models\filters;

use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DotPlant\EntityStructure\models\BaseStructure;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dotplant_store_filter_sets}}".
 *
 * @property integer $id
 * @property integer $structure_id
 * @property integer $property_id
 * @property integer $sort_order
 * @property integer $delegate_to_child
 * @property integer $group_id
 *
 * @property PropertyGroup $group
 * @property Property $property
 * @property BaseStructure $structure
 * @property FilterStaticValueModel[] $filterStaticValues
 */
class FilterSetsModel extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_filter_sets}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['structure_id', 'property_id', 'sort_order', 'delegate_to_child', 'group_id'], 'required'],
            [['structure_id', 'property_id', 'sort_order', 'delegate_to_child', 'group_id'], 'integer'],
            [
                ['group_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => PropertyGroup::className(),
                'targetAttribute' => ['group_id' => 'id'],
            ],
            [
                ['property_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Property::className(),
                'targetAttribute' => ['property_id' => 'id'],
            ],
            [
                ['structure_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BaseStructure::className(),
                'targetAttribute' => ['structure_id' => 'id'],
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
            'structure_id' => Yii::t('app', 'Structure ID'),
            'property_id' => Yii::t('app', 'Property ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'delegate_to_child' => Yii::t('app', 'Delegate To Child'),
            'group_id' => Yii::t('app', 'Group ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStructure()
    {
        return $this->hasOne(BaseStructure::className(), ['id' => 'structure_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilterStaticValues()
    {
        return $this->hasMany(FilterStaticValueModel::className(), ['filter_set_id' => 'id']);
    }
}
