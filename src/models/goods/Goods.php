<?php

namespace DotPlant\Store\models\goods;

use DevGroup\DataStructure\behaviors\HasProperties;
use DevGroup\DataStructure\traits\PropertiesTrait;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\Store\exceptions\GoodsException;
use DotPlant\Store\interfaces\GoodsInterface;
use DotPlant\Store\interfaces\GoodsTypesInterface;
use DotPlant\Store\models\price\DummyPrice;
use DotPlant\Store\models\price\Price;
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
class Goods extends ActiveRecord implements GoodsInterface, GoodsTypesInterface
{
    use MultilingualTrait;
    use TagDependencyTrait;
    use PropertiesTrait;

    /**
     *
     * @var null
     */
    public $priceClass = null;
    public $visibilityType = null;

    /**
     * Whether can we apply measures for product
     *
     * @var bool | null
     */
    public $isMeasurable = null;

    /**
     * Whether can we download product
     *
     * @var bool | null
     */
    public $isDownloadable = null;

    /**
     * Whether can use product if filters or in search
     *
     * @var bool | null
     */
    public $isFilterable = null;

    /**
     * Whether product is option
     *
     * @var bool | null
     */
    public $isService = null;

    /**
     * Whether product is option
     *
     * @var bool | null
     */
    public $isOption = null;

    /**
     * Whether product is part
     *
     * @var bool | null
     */
    public $isPart = null;

    /**
     * Whether product has options
     *
     * @var bool | null
     */
    public $hasOptions = null;

    /** @var  Price | null */
    protected $_price = null;
    /**
     * Type to class associations
     *
     * @var array
     */
    private static $_goodsMap = [
        self::TYPE_PRODUCT => Product::class,
        self::TYPE_BUNDLE => Bundle::class,
        self::TYPE_SET => Set::class,
        self::TYPE_PART => Part::class,
        self::TYPE_OPTION => Option::class,
        self::TYPE_SERVICE => Service::class,
        self::TYPE_FILE => File::class,
    ];

    /**
     * @inheritdoc
     */
    public static function getRoles()
    {
        return [
            self::TYPE_PART => Yii::t('dotplant.store', 'Part'),
            self::TYPE_OPTION => Yii::t('dotplant.store', 'Option'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getTypes()
    {
        return [
            self::TYPE_PRODUCT => Yii::t('dotplant.store', 'Product'),
            self::TYPE_BUNDLE => Yii::t('dotplant.store', 'Bundle'),
            self::TYPE_SET => Yii::t('dotplant.store', 'Set'),
            self::TYPE_PART => Yii::t('dotplant.store', 'Part'),
            self::TYPE_OPTION => Yii::t('dotplant.store', 'Option'),
            self::TYPE_SERVICE => Yii::t('dotplant.store', 'Service'),
            self::TYPE_FILE => Yii::t('dotplant.store', 'File'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return empty($this->role) ? $this->type : $this->role;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        if (true === in_array($type, static::getTypes())) {
            $this->type = $type;
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function setRole($role)
    {
        if (true === in_array($role, static::getRoles())) {
            $this->role = $role;
            return true;
        }
        return false;
    }

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
            'seller_id' => Yii::t('dotplant.store', 'Seller'),
            'vendor_id' => Yii::t('dotplant.store', 'Vendor'),
            'parent_id' => Yii::t('dotplant.store', 'Parent'),
            'main_structure_id' => Yii::t('dotplant.store', 'Main structure ID'),
            'type' => Yii::t('dotplant.store', 'Type'),
            'role' => Yii::t('dotplant.store', 'Role'),
            'sku' => Yii::t('dotplant.store', 'Sku'),
            'inner_sku' => Yii::t('dotplant.store', 'Inner sku'),
            'is_deleted' => Yii::t('dotplant.store', 'Is deleted'),
            'created_at' => Yii::t('dotplant.store', 'Created at'),
            'created_by' => Yii::t('dotplant.store', 'Created by'),
            'updated_at' => Yii::t('dotplant.store', 'Updated at'),
            'updated_by' => Yii::t('dotplant.store', 'Updated by'),
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

    /**
     * @inheritdoc
     */
    public static function create($type = self::TYPE_PRODUCT)
    {
        if (false === isset(static::getTypes()[$type])) {
            throw new GoodsException(
                Yii::t('dotplant.store', 'Attempting to create unknown type of goods')
            );
        }
        $goodsClass = self::$_goodsMap[$type];
        /** @var Goods $goods */
        $goods = new $goodsClass;
        $goods->type = $type;
        if (null !== $goods->priceClass) {
            /** @var Price $priceClass */
            $priceClass = $goods->priceClass;
            $goods->_price = $priceClass::create();
        } else {
            $goods->_price = new DummyPrice();
        }
        return $goods;
    }
}
