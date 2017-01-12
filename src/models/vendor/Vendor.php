<?php

namespace DotPlant\Store\models\vendor;

use DevGroup\AdminUtils\traits\FetchModels;
use DevGroup\DataStructure\traits\PropertiesTrait;
use DevGroup\Entity\traits\BaseActionsInfoTrait;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use DevGroup\Entity\traits\SoftDeleteTrait;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\NamingHelper;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\EntityStructure\interfaces\MainEntitySeoInterface;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\EntityStructure\traits\MainEntitySeoTrait;
use DotPlant\Monster\Universal\MonsterEntityTrait;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

/**
 * This is the model class for table "{{%dotplant_store_vendor}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 * @property integer $is_deleted
 * @property string $packed_json_data
 */
class Vendor extends ActiveRecord implements MainEntitySeoInterface
{
    use MultilingualTrait;
    use TagDependencyTrait;
    use FetchModels;
    use EntityTrait;
    use SeoTrait;
    use SoftDeleteTrait;
    use BaseActionsInfoTrait;
    use PropertiesTrait;
    use MonsterEntityTrait;
    use MainEntitySeoTrait;

    public static $listCache;

    public $template_id;
    public $providers = [];
    public $content = [];
    public $layout_id;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationModelClass' => VendorTranslation::class,
                'translationPublishedAttribute' => 'is_active'
            ],
            'CacheableActiveRecord' => [
                'class' => CacheableActiveRecord::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_vendor}}';
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        return [
            [['name'], 'required'],
            [['created_at', 'created_by', 'updated_at', 'updated_by', 'is_deleted'], 'integer'],
            [['packed_json_data'], 'string'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * Override safe attributes to include translation attributes
     *
     * @return array
     */
    public function safeAttributes()
    {
        $t = new VendorTranslation();
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
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    public static function find()
    {
        /** @var ActiveQuery $query */
        $query = Yii::createObject(ActiveQuery::className(), [get_called_class()]);
        return $query = $query->innerJoinWith(['defaultTranslation']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'name' => Yii::t('dotplant.store', 'Name'),
            'created_at' => Yii::t('dotplant.store', 'Created At'),
            'created_by' => Yii::t('dotplant.store', 'Created By'),
            'updated_at' => Yii::t('dotplant.store', 'Updated At'),
            'updated_by' => Yii::t('dotplant.store', 'Updated By'),
            'packed_json_data' => Yii::t('dotplant.store', 'Packed Json Data'),
        ];
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
        $dataProvider->sort->attributes['title'] = [
            'asc' => [VendorTranslation::tableName() . '.title' => SORT_ASC],
            'desc' => [VendorTranslation::tableName() . '.title' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['is_active'] = [
            'asc' => [VendorTranslation::tableName() . '.is_active' => SORT_ASC],
            'desc' => [VendorTranslation::tableName() . '.is_active' => SORT_DESC],
        ];
        $dataProvider->sort->attributes['slug'] = [
            'asc' => [VendorTranslation::tableName() . '.slug' => SORT_ASC],
            'desc' => [VendorTranslation::tableName() . '.slug' => SORT_DESC],
        ];
        if (false === $this->load($params)) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['is_deleted' => $this->is_deleted]);
        $translation = new VendorTranslation();
        if (false === $translation->load(static::fetchParams($params, static::class, $translation))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['like', VendorTranslation::tableName() . '.name', $this->name]);
        $query->andFilterWhere(['like', VendorTranslation::tableName() . '.title', $this->title]);
        $query->andFilterWhere(['like', VendorTranslation::tableName() . '.h1', $this->h1]);
        $query->andFilterWhere(['like', VendorTranslation::tableName() . '.slug', $this->slug]);
        $query->andFilterWhere([VendorTranslation::tableName() . '.is_active' => $this->is_active]);
        return $dataProvider;
    }

    /**
     * @return array|mixed
     */
    public static function getArrayList($force = false)
    {
        if ($force === false && static::$listCache !== null) {
            return static::$listCache;
        }

        $cacheKey = 'VendorDropDownLis';
        $list = Yii::$app->cache->get($cacheKey);
        if (false === $list || $force === true) {
            $list = self::find()->select('name')->indexBy('id')->column();
            if (false === empty($list)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $list,
                    86400,
                    new TagDependency(['tags' => [
                        NamingHelper::getCommonTag(self::class),
                    ]])
                );
                static::$listCache = $list;
            }
        }
        return (false === $list) ? [] : $list;
    }

    public static function getOrCreate($name)
    {
        $allVendors = static::getArrayList();
        if (in_array($name, $allVendors, true)) {
            return (int) array_search($name, $allVendors, true);
        }
        $vendor = new Vendor();
        $vendor->name = $name;
        $vendor->slug =  Inflector::slug($name);;
        if ($vendor->save()===false) {
            var_dump($vendor->errors);
        }
        foreach (Yii::$app->multilingual->getAllLanguages() as $lang) {
            /** @var VendorTranslation $translation */
            $translation = $vendor->translate($lang->id);
            $translation->model_id = $vendor->id;
            $translation->title =
                $translation->h1 =
                $translation->breadcrumbs_label =
                $translation->meta_description =
                $name;
            $translation->slug = Inflector::slug($name);
            $translation->save();
        }
        static::getArrayList(true);
        return $vendor->id;
    }

    /**
     * @return array
     */
    public function getSeoBreadcrumbs()
    {
        $breadcrumbs[] = [
            'label' => $this->defaultTranslation->breadcrumbs_label,
        ];
        return $breadcrumbs;
    }
}
