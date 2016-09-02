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
use DotPlant\Store\models\price\Price;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\web\NotFoundHttpException;

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
    protected $priceClass = null;
    protected $visibilityType = null;

    /**
     * Whether can we apply measures for product
     *
     * @var bool | null
     */
    protected $isMeasurable = null;

    /**
     * Whether can we download product
     *
     * @var bool | null
     */
    protected $isDownloadable = null;

    /**
     * Whether can use product if filters or in search
     *
     * @var bool | null
     */
    protected $isFilterable = null;

    /**
     * Whether product is option
     *
     * @var bool | null
     */
    protected $isService = null;

    /**
     * Whether product is option
     *
     * @var bool | null
     */
    protected $isOption = null;

    /**
     * Whether product is part
     *
     * @var bool | null
     */
    protected $isPart = null;

    /**
     * Whether product has options
     *
     * @var bool | null
     */
    protected $hasOptions = null;

    /** @var  Price | null */
    protected $price = null;

    /**
     * @inheritdoc
     */
    protected static $tablePrefix = 'dotplant_store_goods';

    /**
     * Workaround for DataStructureTools to store all goods properties it the one table set
     *
     * @return mixed
     */
    public static function getApplicableClass()
    {
        return self::class;
    }

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

    public function getPrice()
    {
        return $this->price;
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
                        'vendor_id',
                        'parent_id',
                        'main_structure_id',
                        'type',
                        'role',
                    ],
                    'integer'
                ],
                [['sku'], 'required'],
                [['sku', 'inner_sku'], 'string', 'max' => 255],
            ],
            $this->propertiesRules()
        );
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
     *
     * @return array
     */
    public function safeAttributes()
    {
        $t = new GoodsTranslation();
        return ArrayHelper::merge(parent::safeAttributes(), $t->safeAttributes());
    }

    /**
     * Override for filtering in grid
     *
     * @param string $attribute
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
    public static function create($type)
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
        self::injectPriceObject($goods);
        return $goods;
    }

    /**
     * @inheritdoc
     */
    public static function get($id)
    {
        $goods = null;
        $record = self::find()->where(['id' => $id])->asArray(true)->one();
        if (null !== $record) {
            $type = empty($record['role']) ? $record['type'] : $record['role'];
            if (false === isset(self::$_goodsMap[$type])) {
                throw new GoodsException(
                    Yii::t('dotplant.store', 'Attempting to create unknown type of goods')
                );
            }
            $productClass = self::$_goodsMap[$type];
            /** @var self $model */
            $goods = new $productClass;
            self::populateRecord($goods, $record);
            self::injectPriceObject($goods);
        }
        return $goods;
    }

    /**
     * Injects according Price object into Goods model for further calculations
     *
     * @param $goods
     * @throws GoodsException
     * @throws \DotPlant\Store\exceptions\PriceException
     */
    private static function injectPriceObject($goods)
    {
        if (null !== $goods->priceClass) {
            /** @var Price $priceClass */
            $priceClass = $goods->priceClass;
            $goods->price = $priceClass::create($goods);
        } else {
            throw new GoodsException(
                Yii::t('dotplant.store', '\'priceClass\' property myst be valid heir of Price')
            );
        }
    }

    /**
     * Finds models
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query ActiveQuery */

        $dataProvider = new ActiveDataProvider([
            'query' => $query = static::find(),
            'pagination' => [
                //TODO configure it
                'pageSize' => 15
            ],
        ]);
        if (null != $this->parent_id) {
            $query->andWhere(['parent_id' => $this->parent_id]);
        }
        $dataProvider->sort->attributes['name'] = [
            'asc' => [GoodsTranslation::tableName() . '.name' => SORT_ASC],
            'desc' => [GoodsTranslation::tableName() . '.name' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['title'] = [
            'asc' => [GoodsTranslation::tableName() . '.title' => SORT_ASC],
            'desc' => [GoodsTranslation::tableName() . '.title' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['is_active'] = [
            'asc' => [GoodsTranslation::tableName() . '.is_active' => SORT_ASC],
            'desc' => [GoodsTranslation::tableName() . '.is_active' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['slug'] = [
            'asc' => [GoodsTranslation::tableName() . '.slug' => SORT_ASC],
            'desc' => [GoodsTranslation::tableName() . '.slug' => SORT_DESC],
        ];
        if (false === $this->load($params)) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['is_deleted' => $this->is_deleted]);
        $translation = new GoodsTranslation();
        if (false === $translation->load(static::fetchParams($params, static::class, $translation))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like', GoodsTranslation::tableName() . '.name', $this->name]);
        $query->andFilterWhere(['like', GoodsTranslation::tableName() . '.title', $this->title]);
        $query->andFilterWhere(['like', GoodsTranslation::tableName() . '.h1', $this->h1]);
        $query->andFilterWhere(['like', GoodsTranslation::tableName() . '.slug', $this->slug]);
        $query->andFilterWhere([GoodsTranslation::tableName() . '.is_active' => $this->is_active]);
        return $dataProvider;
    }

    /**
     * Workaround to have ability use Model::load() method instead assigning values from request by hand
     *
     * @param array $params
     * @param string $fromClass class name
     * @param ActiveRecord $toModel
     * @return array
     */
    public static function fetchParams($params, $fromClass, $toModel)
    {
        if (true === empty($params)
            || false === class_exists($fromClass)
            || false === $toModel instanceof ActiveRecord
        ) {
            return [];
        }
        $outParams = [];
        $toClass = get_class($toModel);
        $fromName = array_pop(explode('\\', $fromClass));
        $toName = array_pop(explode('\\', $toClass));
        if (true === isset($params[$fromName])) {
            foreach ($params[$fromName] as $key => $value) {
                if (true === in_array($key, $toModel->attributes())) {
                    $outParams[$toName][$key] = $value;
                }
            }
        }
        return $outParams;
    }
}
