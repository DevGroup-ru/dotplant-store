<?php

namespace DotPlant\Store\models;

use DevGroup\DataStructure\behaviors\HasProperties;
use DevGroup\DataStructure\traits\PropertiesTrait;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\Store\interfaces\GoodsInterface;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%dotplant_goods}}".
 *
 * @property integer $id
 * @property integer $seller_id
 * @property integer $vendor_id
 * @property integer $parent_id
 * @property integer $main_structure_id
 * @property integer $type
 * @property integer $role
 * @property string $sku
 * @property string $inner_sku
 * @property integer $is_deleted
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property Goods[] $children
 * @property Goods $parent
 */
class Goods extends ActiveRecord implements GoodsInterface
{
    use MultilingualTrait;
    use TagDependencyTrait;
    use PropertiesTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationModelClass' => GoodsTranslation::class,
                'translationPublishedAttribute' => 'is_active'
            ],
            'CacheableActiveRecord' => [
                'class' => CacheableActiveRecord::class,
            ],
            'properties' => [
                'class' => HasProperties::class,
                'autoFetchProperties' => true,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return ArrayHelper::merge(
            [
                [
                    [
                        'seller_id',
                        'vendor_id',
                        'parent_id',
                        'main_structure_id',
                        'type',
                        'role',
                        'is_deleted',
                        'created_at',
                        'created_by',
                        'updated_at',
                        'updated_by'
                    ],
                    'integer'
                ],
                [['sku'], 'required'],
                [['sku', 'inner_sku'], 'string', 'max' => 255],
            ],
            $this->propertiesRules());
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'seller_id' => Yii::t('dotplant.store', 'Seller ID'),
            'vendor_id' => Yii::t('dotplant.store', 'Vendor ID'),
            'parent_id' => Yii::t('dotplant.store', 'Parent ID'),
            'main_structure_id' => Yii::t('dotplant.store', 'Main Structure ID'),
            'type' => Yii::t('dotplant.store', 'Type'),
            'role' => Yii::t('dotplant.store', 'Role'),
            'sku' => Yii::t('dotplant.store', 'Sku'),
            'inner_sku' => Yii::t('dotplant.store', 'Inner Sku'),
            'is_deleted' => Yii::t('dotplant.store', 'Is Deleted'),
            'created_at' => Yii::t('dotplant.store', 'Created At'),
            'created_by' => Yii::t('dotplant.store', 'Created By'),
            'updated_at' => Yii::t('dotplant.store', 'Updated At'),
            'updated_by' => Yii::t('dotplant.store', 'Updated By'),
        ];
    }

    /**
     * Override safe attributes to include translation attributes
     * @return array
     */
    public function safeAttributes()
    {
        $t = new GoodsTranslation();
        return ArrayHelper::merge(static::safeAttributes(), $t->safeAttributes());
    }

    /**
     * Override for filtering in grid
     * @param string $attribute
     *
     * @return bool
     */
    public function isAttributeActive($attribute)
    {
        return in_array($attribute, $this->safeAttributes());
    }

    /**
     * Override Multilingual find method to include unpublished records
     *
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @var ActiveQuery $query */
        $query = Yii::createObject(ActiveQuery::className(), [get_called_class()]);
        return $query = $query->innerJoinWith(['defaultTranslation']);
    }

    /**
     * @return ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(static::class, ['id' => 'parent_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(static::class, ['parent_id' => 'id']);
    }
}
