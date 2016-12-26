<?php

namespace DotPlant\Store\providers;

use DevGroup\DataStructure\helpers\PropertyStorageHelper;
use DevGroup\DataStructure\models\ApplicablePropertyModels;
use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\PropertyPropertyGroup;
use DevGroup\DataStructure\models\PropertyStorage;
use DevGroup\MediaStorage\components\GlideConfigurator;
use DevGroup\MediaStorage\helpers\MediaTableGenerator;
use DevGroup\MediaStorage\models\Media;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsParent;
use DotPlant\Store\repository\GoodsMainCategoryRepositoryInterface;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\VarDumper;

abstract class BaseGoodsProvider extends DataEntityProvider
{
    private $_children = [];
    private $_goods = [];
    private $_properties = [];
    private $_images = [];

    // @todo: set right default values. Now we have random values for testing
    public $withChildren = true;
    // properties
    public $childrenProperties = true;
    public $implodePropertyValues = true;
    public $propertyValuesDelimiter = ', ';
    // images
    public $withImages = true;
    public $childrenImages = true;
    public $imagePropertyKey = 'goods_images';
    public $thumbnailSize = [100, 100];
    public $thumbnailCropMode = GlideConfigurator::FIT_CONTAIN;
    public $thumbnailQuality = 90;
    public $collectChildrenImages = true;

    public function getGoods($ids)
    {
        $this->prepareGoods($ids);
        //
        $goods = [];
        foreach ($ids as $id) {
            $singleGoods = $this->buildGoods($id);
            if ($singleGoods !== null) {
                $goods[] = $singleGoods;
            }
        }
        return $goods;
    }

    protected function buildGoods($id)
    {
        if (!isset($this->_goods[$id])) {
            return null;
        }
        $singleGoods = $this->_goods[$id];
        $singleGoods['url'] = Url::toRoute(
            [
                'universal/show',
                'entities' => [
                    BaseStructure::class => [
                        \Yii::$container
                            ->get(GoodsMainCategoryRepositoryInterface::class, [$id])
                            ->loadGoodsMainCategory(\Yii::$app->multilingual->context_id)
                            ->id,
                    ],
                    Goods::class => [
                        $id,
                    ],
                ],
            ]
        );
        if ($singleGoods !== null) {
            $singleGoods['children'] = [];
            if ($this->withChildren && isset($this->_children[$id])) {
                foreach ($this->_children[$id] as $childId) {
                    $childGoods = $this->buildGoods($childId);
                    if ($childGoods !== null) {
                        $singleGoods['children'][] = $childGoods;
                    }
                }
            }
            $singleGoods['properties'] = isset($this->_properties[$id]) ? $this->_properties[$id] : [];
            $singleGoods['images'] = isset($this->_images[$id]) ? $this->_images[$id] : [];
        }
        return $singleGoods;
    }

    protected function prepareGoods($ids)
    {
        $allIds = $ids;
        // get children ids
        if ($this->withChildren) {
            $children = GoodsParent::find()
                ->where(['goods_parent_id' => $ids])
                ->asArray(true)
                ->all();
            $this->_children = [];
            foreach ($ids as $id) {
                $this->_children[$id] = [];
            }
            foreach ($children as $child) {
                if ($child['goods_parent_id'] != null) {
                    $this->_children[$child['goods_parent_id']][] = $child['goods_id'];
                    $allIds[] = $child['goods_id'];
                }
            }
        }
        // get all models
        $this->_goods = Goods::find()
            ->where(['id' => $allIds])
            ->indexBy('id')
            ->asArray(true)
            ->all();
        $this->getProperties($this->childrenProperties ? $allIds : $ids);
        $this->getImages($this->childrenImages ? $allIds : $ids);
    }

    protected function getProperties($ids)
    {
        /*$apmId = ApplicablePropertyModels::getIdForClass(Goods::class);
        if ($apmId === false) {
            return [];
        }
        // get pg by apm
        $propertyGroups = PropertyGroup::find()
            ->select(['name', 'id'])
            ->where(['applicable_property_model_id' => $apmId])
            ->orderBy(['sort_order' => SORT_ASC])
            ->indexBy('id')
            ->asArray(true)
            ->column();
        // get ppg
        $ppg = PropertyPropertyGroup::find()
            ->select(['property_group_id', 'property_id'])
            ->distinct(true)
            ->orderBy(['sort_order_group_properties' => SORT_ASC, 'property_id' => SORT_ASC])
            ->where(['property_group_id' => array_keys($propertyGroups)])
            ->indexBy('property_id')
            ->column();*/
        // @todo: implement sorting
        $storageHandlers = PropertyStorage::find()->all();
        foreach ($storageHandlers as $storageHandler) {
            if (method_exists($storageHandler->class_name, 'getFrontendValues')) {
                $values = call_user_func([$storageHandler->class_name, 'getFrontendValues'], Goods::class, $ids);
                foreach ($values as $modelId => $properties) {
                    foreach ($properties as $propertyId => $values) {
                        if (!isset($this->_properties[$modelId])) {
                            $this->_properties[$modelId] = [];
                        }
                        $property = Property::findById($propertyId);
                        if ($property === null) {
                            continue;
                        }
                        $this->_properties[$modelId][$propertyId] = [
                            'key' => $property->key,
                            'label' => $property->defaultTranslation->name,
                            'value' => $this->implodePropertyValues
                                ? implode($this->propertyValuesDelimiter, (array) $values)
                                : $values,
                        ];
                    }
                }
            }
        }
    }

    protected function getImages($ids)
    {
        // @todo: implement via media helper
        $this->_images = [];
        $property = Property::findOne(['key' => $this->imagePropertyKey]);
        if ($property !== null) {
            $tableName = (new MediaTableGenerator(['db' => Goods::getDb()]))
                ->getMediaTableName(Goods::class);
            $thumbnailOptions = [];
            if (count($this->thumbnailSize) == 2) {
                list($w, $h) = $this->thumbnailSize;
                $thumbnailOptions = (new GlideConfigurator($w, $h, $this->thumbnailCropMode, $this->thumbnailQuality))
                    ->getConfiguration();
            }
            $this->_images = (new Query())
                ->select(['media_id', 'model_id', 'path', 'alt', 'title'])
                ->from($tableName)
                ->where(
                    [
                        'and',
                        [
                            'model_id' => $ids,
                            'property_id' => $property->id,
                        ],
                        [
                            'like',
                            'mime',
                            'image/',
                        ],
                    ]
                )
                ->innerJoin(Media::tableName(), 'media_id = id')
                ->orderBy(['sort_order' => SORT_ASC])
                ->all();
            if ($this->collectChildrenImages) {
                foreach ($this->_children as $parentId => $children) {
                    foreach ($this->_images as $image) {
                        if (in_array((string) $image['model_id'], $children)) {
                            $image['model_id'] = (string) $parentId;
                            $this->_images[] = $image;
                        }
                    }
                }
            }
            $this->_images = ArrayHelper::map(
                $this->_images,
                'media_id',
                function ($item) use ($thumbnailOptions) {
                    $item['src'] = Url::to(['/media/file/send', 'mediaId' => $item['model_id']]);
                    $item['thumbnailSrc'] = Url::to(
                        [
                            '/media/file/send',
                            'mediaId' => $item['media_id'],
                            'config' => ['imageConfig' => $thumbnailOptions],
                        ]
                    );
                    return $item;
                },
                'model_id'
            );
        }
    }
}
