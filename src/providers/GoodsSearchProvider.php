<?php

namespace DotPlant\Store\providers;

use DevGroup\DataStructure\search\components\Search;
use DotPlant\Monster\DataEntity\DataEntityProvider;
use DotPlant\Store\models\goods\CategoryGoods;
use DotPlant\Store\models\goods\Goods;
use DevGroup\DataStructure\models\Property;
use DevGroup\MediaStorage\components\GlideConfigurator;
use DevGroup\MediaStorage\helpers\MediaHelper;
use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\models\goods\GoodsCategoryExtended;
use DotPlant\Store\models\price\Price;
use DotPlant\Store\models\price\ProductPrice;
use yii\helpers\Url;
use DotPlant\EntityStructure\models\BaseStructure;
use DevGroup\DataStructure\helpers\PropertiesHelper;
use yii\data\Pagination;
use yii\data\Sort;
use DotPlant\EntityStructure\helpers\PaginationHelper;
use DotPlant\Store\components\Store;

class GoodsSearchProvider extends DataEntityProvider
{
    /**
     * @var string the region key
     */
    public $regionKey = 'goodsSearchRegion';

    /**
     * @var string the material key
     */
    public $materialKey = 'goodsSearchMaterial';

    /**
     * @var string the current action route
     */
    public $actionRoute = ['/store/goods/search'];

    /**
     * @var string the block key
     */
    public $blockKey = 'goods';

    /**
     * @var string the pagination block key
     */
    public $paginationKey = 'pagination';

    /**
     * @var string the pagination block key
     */
    public $totalCountKey = 'totalCount';

    /**
     * @var string the categories block key
     */
    public $categoriesKey = 'categories';

    /**
     * @var string the image property key
     */
    public $imagePropertyKey = 'goods_images';

    /**
     * @var int thumb width
     */
    public $thumbWidth = 262;

    /**
     * @var int thump height
     */
    public $thumbHeight = 175;

    /**
     * @var int min goods per page
     */
    public $minLimit = 1;

    /**
     * @var int max goods per page
     */
    public $maxLimit = 12;

    /**
     * @var bool|int parent id
     */
    public $parentId = false;

    /**
     * @var string the pagination get parameter
     */
    public $paginationParameter = 'page';

    /**
     * @var string the search get parameter
     */
    public $searchParameter = 'search';

    /**
     * @var string the limit get parameter
     */
    public $limitParameter = 'limit';

    /**
     * @var string the limit get parameter
     */
    public $parentIdParameter = 'parent-id';

    /**
     * @var string the filter by the property get parameter
     */
    public $propertyParameter = 'properties';

    /**
     * @var string the sort get parameter
     */
    public $sortParameter = 'sort';

    /**
     * @var array sort fields
     */
    public $sortFields = ['retail_price', 'id'];


    /**
     * @return array
     */
    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }

    /**
     * @param $good
     * @return array
     */
    private function buildGoods($good)
    {
        $languageId = \Yii::$app->multilingual->language_id;
        $currency = CurrencyHelper::getUserCurrency();

        if (!empty($this->imagePropertyKey)) {
            $imageProperty = Property::findOne(['key' => $this->imagePropertyKey]);
            $images = MediaHelper::getMediaData(
                $good,
                $imageProperty,
                new GlideConfigurator($this->thumbWidth, $this->thumbHeight)
            );
            $image = reset($images);
        }

        ProductPrice::create($good);
        $price = $good->getMinPrice(
            Store::isRetail() ? Price::TYPE_RETAIL : Price::TYPE_WHOLESALE,
            true,
            $currency->iso_code
        );

        $properties = [];

        foreach ($good->propertiesValues as $propertyId => $propertyValue) {
            if (isset($propertyValue[$languageId])) {
                $properties[$good->propertiesAttributes[$propertyId]] = $propertyValue[$languageId][0];
            } else {
                $properties[$good->propertiesAttributes[$propertyId]] = $propertyValue;
            }
        }

        $parent = $good->mainCategory;

        $url = Url::toRoute(
            [
                '/universal/show',
                'entities' => [
                    BaseStructure::class => [
                        $parent->id,
                    ],
                    Goods::class => [
                        $good->id,
                    ],
                ],
            ]
        );

        return [
            'parentId' => $parent->id,
            'id' => $good->id,
            'photo' => isset($image) ? $image['src'] : '',
            'url' => $url,
            'name' => $good['defaultTranslation']['name'],
            'price' => CurrencyHelper::format($price['value'], $currency),
            'oldPrice' => isset($price['discountReasons']) && count($price['discountReasons']) > 0
                ? CurrencyHelper::format($price['valueWithoutDiscount'], $currency)
                : null,
            'properties' => $properties
        ];
    }

    /**
     * @return Sort
     */
    private function getSort()
    {
        $attributes = [];

        foreach ($this->sortFields as $sortField) {
            $attributes[$sortField] = [
                'asc' => [$sortField => SORT_ASC],
                'desc' => [$sortField => SORT_DESC],
            ];
        }

        $sort = new Sort(
            [
                'attributes' => $attributes,
                'sortParam' => $this->sortParameter
            ]
        );

        return $sort;
    }

    /**
     * @param $fullSectionArray
     * @param $sections
     * @return mixed
     */
    private function getTree($fullSectionArray, &$sections)
    {
        foreach ($sections as $key => $section) {
            if (array_key_exists($section['id'], $fullSectionArray)) {
                $sections[$key]['subsections'] = $fullSectionArray[$section['id']];
                $this->getTree($fullSectionArray, $sections[$key]['subsections']);
            }
        }
        return $sections;
    }

    /**
     * @param $goodsIds
     * @return mixed
     */
    private function getGoodsSectionsTree($goodsIds)
    {
        $categoryGoodsModels = CategoryGoods::find()
            ->select('structure_id')
            ->addSelect('goods_id')
            ->where(
                [
                    'goods_id' => $goodsIds
                ]
            )
            ->all();

        $categoryGoods = [];
        foreach ($categoryGoodsModels as $section) {
            $categoryGoods[$section->structure_id][] = $section->goods_id;
        }

        $goodsCategoryModels = BaseStructure::find()
            ->innerJoin(
                GoodsCategoryExtended::tableName(),
                GoodsCategoryExtended::tableName() . '.[[model_id]] = ' . BaseStructure::tableName() . '.[[id]]'
            )
            ->where(
                [
                    'is_active' => 1,
                    'is_deleted' => 0
                ]
            )
            ->all();

        $goodsCategory = [];

        foreach ($goodsCategoryModels as $section) {
            $goodsCategory[$section->parent_id][] = [
                'name' => $section->defaultTranslation->name,
                'id' => $section->id,
                'parentId' => $section->parent_id,
                'slug' => $section->defaultTranslation->slug,
                'count' => isset($categoryGoods[$section->id]) ? count($categoryGoods[$section->id]) : 0
            ];
        }

        $firstCategory = reset($goodsCategory);

        return $this->getTree($goodsCategory, $firstCategory);
    }
    
    // @todo: rewrite this code via search component (yii2-data-structure-tools module)
    public function getEntities(&$actionData)
    {
        if (($searchPhrase = \Yii::$app->request->get($this->searchParameter)) !== null) {
            $limit = \Yii::$app->request->get($this->limitParameter) !== null ? \Yii::$app->request->get($this->limitParameter) : $this->maxLimit;
            $bs = new Search();
            $q = $bs->search(
                Goods::class,
                [
                    'limit' => $limit
                ]
            );

            $mainEntityAttributes = ['type' => Goods::TYPE_PRODUCT];

            $q->pagination(
                [
                    'class' => Pagination::class
                ]
            )->mainEntityAttributes(
                $mainEntityAttributes
            );

            if (($filterProperties = \Yii::$app->request->get($this->propertyParameter)) !== null) {
                $q->properties([Goods::class => $filterProperties]);
            }

            $pagesQuery = clone $q;
            $pages = $pagesQuery->getPagination();

            $q->query()->query->distinct()
                ->leftJoin(
                    '{{%dotplant_store_goods_eav}}',
                    '{{%dotplant_store_goods_eav}}.[[model_id]] = ' . Goods::tableName() . '.[[id]]'
                )
                ->where(
                    [
                        'like', '[[name]]', $searchPhrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[description]]', $searchPhrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[announce]]', $searchPhrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[value_string]]', $searchPhrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[value_text]]', $searchPhrase
                    ]
                )
                ->andWhere(
                    [
                        'is_active' => 1,
                        'is_deleted' => 0,
                        '{{%dotplant_store_goods_translation}}.[[language_id]]' => \Yii::$app->multilingual->language_id
                    ]
                );

            $query = $q->query()->query;
            $countQuery = clone $query;
            $parentId = \Yii::$app->request->get($this->parentIdParameter, $this->parentId);

            if ($parentId !== false) {
                $query->innerJoin(
                    CategoryGoods::tableName(),
                    CategoryGoods::tableName() . '.[[goods_id]] = ' . Goods::tableName() . '.[[id]]'
                )
                    ->andWhere(
                        [
                            'structure_id' => $parentId
                        ]
                    );
            }

            $goodsIds = $countQuery->column();
            $goodsSectionsTree = $this->getGoodsSectionsTree($goodsIds);
            $pagerQuery = clone $query;
            $totalCount = $pagerQuery->count();

            if (isset($pages)) {
                $pagination = PaginationHelper::getItems($pages);
            }


            $query->leftJoin(
                '{{%dotplant_store_goods_warehouse}}',
                '{{%dotplant_store_goods}}.[[id]] = {{%dotplant_store_goods_warehouse}}.[[goods_id]]'
            );

            $sort = $this->getSort();
            if ($sort->orders) {
                $query->orderBy($sort->orders);
            }

            $goods = $query
                ->offset($pages->getOffset())
                ->limit($pages->getLimit())
                ->all();

            $goodsData = [];
            if (!empty($goods)) {
                PropertiesHelper::fillProperties($goods);
                $goodsData = array_map(array($this, 'buildGoods'), $goods);
            }
        }

        $data = [
            $this->blockKey => isset($goodsData) ? $goodsData : [],
            $this->totalCountKey => isset($totalCount) ? $totalCount : 0,
            $this->paginationKey => isset($pagination) ? $pagination : [],
            $this->categoriesKey => isset($goodsSectionsTree) ? $goodsSectionsTree : []
        ];

        return [
            $this->regionKey => [
                $this->materialKey => $data
            ]
        ];
    }
}
