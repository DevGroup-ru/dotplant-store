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
use DotPlant\Store\models\warehouse\GoodsWarehouse;
use yii\helpers\Url;
use DotPlant\EntityStructure\models\BaseStructure;
use DevGroup\DataStructure\helpers\PropertiesHelper;
use yii\data\Pagination;
use yii\data\Sort;
use DotPlant\EntityStructure\helpers\PaginationHelper;
use DotPlant\Store\components\Store;
use DotPlant\Store\models\warehouse\Warehouse;

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




            $query = Goods::find()
                ->distinct()
                ->select($q->query()->query->select)
                ->leftJoin(
                    Goods::eavTable(),
                    Goods::eavTable() . '.[[model_id]] = ' . Goods::tableName() . '.[[id]]'
                )
                ->filterWhere(
                    [
                        'like', '[[name]]', $searchPhrase
                    ]
                )
                ->orFilterWhere(
                    [
                        'like', '[[description]]', $searchPhrase
                    ]
                )
                ->orFilterWhere(
                    [
                        'like', '[[announce]]', $searchPhrase
                    ]
                )
                ->orFilterWhere(
                    [
                        'like', '[[value_string]]', $searchPhrase
                    ]
                )
                ->orFilterWhere(
                    [
                        'like', '[[value_text]]', $searchPhrase
                    ]
                )
                ->andFilterWhere(
                    [
                        'is_active' => 1,
                        'is_deleted' => 0,
                        Goods::getTranslationTableName() . '.[[language_id]]' => \Yii::$app->multilingual->language_id
                    ]
                )
                ->andFilterWhere($q->query()->query->where);

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
                GoodsWarehouse::tableName(),
                Goods::tableName() . '.[[id]] = ' . GoodsWarehouse::tableName() . '.[[goods_id]]'
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

        $dd = 1;
        $dd = array (
            'goods' =>
                array (
                    0 =>
                        array (
                            'parentId' => 136,
                            'id' => 1,
                            'photo' => NULL,
                            'url' => '/sinnister-things/test-goods',
                            'name' => 'Test goods',
                            'price' => '6 283.53 руб.',
                            'oldPrice' => NULL,
                            'properties' =>
                                array (
                                ),
                        ),
                ),
            'totalCount' => '1',
            'pagination' =>
                array (
                    0 =>
                        array (
                            'label' => '&lt;',
                            'url' => '/search?search=test&send=search&page=1',
                            'class' => 'prev disabled',
                        ),
                    1 =>
                        array (
                            'label' => 1,
                            'url' => '/search?search=test&send=search&page=1',
                            'class' => ' active',
                        ),
                    2 =>
                        array (
                            'label' => 2,
                            'url' => '/search?search=test&send=search&page=2',
                            'class' => '',
                        ),
                    3 =>
                        array (
                            'label' => 3,
                            'url' => '/search?search=test&send=search&page=3',
                            'class' => '',
                        ),
                    4 =>
                        array (
                            'label' => 4,
                            'url' => '/search?search=test&send=search&page=4',
                            'class' => '',
                        ),
                    5 =>
                        array (
                            'label' => 5,
                            'url' => '/search?search=test&send=search&page=5',
                            'class' => '',
                        ),
                    6 =>
                        array (
                            'label' => '&gt;',
                            'url' => '/search?search=test&send=search&page=2',
                            'class' => 'next',
                        ),
                ),
            'categories' =>
                array (
                    0 =>
                        array (
                            'name' => 'Apparel',
                            'id' => 3,
                            'parentId' => NULL,
                            'slug' => 'apparel',
                            'count' => 1,
                            'subsections' =>
                                array (
                                    0 =>
                                        array (
                                            'name' => 'Womens',
                                            'id' => 7,
                                            'parentId' => 3,
                                            'slug' => 'womens',
                                            'count' => 0,
                                            'subsections' =>
                                                array (
                                                    0 =>
                                                        array (
                                                            'name' => 'Tops',
                                                            'id' => 8,
                                                            'parentId' => 7,
                                                            'slug' => 'tops',
                                                            'count' => 0,
                                                            'subsections' =>
                                                                array (
                                                                    0 =>
                                                                        array (
                                                                            'name' => 'Shirts',
                                                                            'id' => 10,
                                                                            'parentId' => 8,
                                                                            'slug' => 'shirts',
                                                                            'count' => 0,
                                                                        ),
                                                                ),
                                                        ),
                                                    1 =>
                                                        array (
                                                            'name' => 'Intimates',
                                                            'id' => 11,
                                                            'parentId' => 7,
                                                            'slug' => 'intimates',
                                                            'count' => 0,
                                                            'subsections' =>
                                                                array (
                                                                    0 =>
                                                                        array (
                                                                            'name' => 'Bras',
                                                                            'id' => 12,
                                                                            'parentId' => 11,
                                                                            'slug' => 'bras',
                                                                            'count' => 0,
                                                                        ),
                                                                    1 =>
                                                                        array (
                                                                            'name' => 'Panties',
                                                                            'id' => 13,
                                                                            'parentId' => 11,
                                                                            'slug' => 'panties',
                                                                            'count' => 0,
                                                                        ),
                                                                    2 =>
                                                                        array (
                                                                            'name' => 'Bralettes',
                                                                            'id' => 191,
                                                                            'parentId' => 11,
                                                                            'slug' => 'bralettes',
                                                                            'count' => 0,
                                                                        ),
                                                                    3 =>
                                                                        array (
                                                                            'name' => 'Shapewear',
                                                                            'id' => 192,
                                                                            'parentId' => 11,
                                                                            'slug' => 'shapewear',
                                                                            'count' => 0,
                                                                        ),
                                                                    4 =>
                                                                        array (
                                                                            'name' => 'Accessories',
                                                                            'id' => 193,
                                                                            'parentId' => 11,
                                                                            'slug' => 'accessories',
                                                                            'count' => 0,
                                                                        ),
                                                                ),
                                                        ),
                                                    2 =>
                                                        array (
                                                            'name' => 'Activewear',
                                                            'id' => 194,
                                                            'parentId' => 7,
                                                            'slug' => 'activewear',
                                                            'count' => 0,
                                                            'subsections' =>
                                                                array (
                                                                    0 =>
                                                                        array (
                                                                            'name' => 'Sports Bras',
                                                                            'id' => 197,
                                                                            'parentId' => 194,
                                                                            'slug' => 'sports-bras',
                                                                            'count' => 0,
                                                                        ),
                                                                    1 =>
                                                                        array (
                                                                            'name' => 'Leggings',
                                                                            'id' => 198,
                                                                            'parentId' => 194,
                                                                            'slug' => 'leggings',
                                                                            'count' => 0,
                                                                        ),
                                                                ),
                                                        ),
                                                    3 =>
                                                        array (
                                                            'name' => 'Legwear',
                                                            'id' => 199,
                                                            'parentId' => 7,
                                                            'slug' => 'legwear',
                                                            'count' => 0,
                                                            'subsections' =>
                                                                array (
                                                                    0 =>
                                                                        array (
                                                                            'name' => 'Socks',
                                                                            'id' => 200,
                                                                            'parentId' => 199,
                                                                            'slug' => 'socks',
                                                                            'count' => 0,
                                                                        ),
                                                                ),
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                    1 =>
                        array (
                            'name' => 'Accessories',
                            'id' => 31,
                            'parentId' => NULL,
                            'slug' => 'accessories',
                            'count' => 0,
                            'subsections' =>
                                array (
                                    0 =>
                                        array (
                                            'name' => 'Jewelry',
                                            'id' => 32,
                                            'parentId' => 31,
                                            'slug' => 'jewelry',
                                            'count' => 0,
                                            'subsections' =>
                                                array (
                                                    0 =>
                                                        array (
                                                            'name' => 'Earrings',
                                                            'id' => 34,
                                                            'parentId' => 32,
                                                            'slug' => 'earrings',
                                                            'count' => 0,
                                                        ),
                                                    1 =>
                                                        array (
                                                            'name' => 'Body Jewelry',
                                                            'id' => 37,
                                                            'parentId' => 32,
                                                            'slug' => 'body-jewelry',
                                                            'count' => 0,
                                                        ),
                                                ),
                                        ),
                                    1 =>
                                        array (
                                            'name' => 'Eyewear',
                                            'id' => 43,
                                            'parentId' => 31,
                                            'slug' => 'eyewear',
                                            'count' => 0,
                                        ),
                                ),
                        ),
                    2 =>
                        array (
                            'name' => 'Licensed',
                            'id' => 212,
                            'parentId' => NULL,
                            'slug' => 'licensed',
                            'count' => 0,
                            'subsections' =>
                                array (
                                    0 =>
                                        array (
                                            'name' => 'Jewelry',
                                            'id' => 214,
                                            'parentId' => 212,
                                            'slug' => 'jewelry',
                                            'count' => 0,
                                            'subsections' =>
                                                array (
                                                    0 =>
                                                        array (
                                                            'name' => 'Earrings',
                                                            'id' => 225,
                                                            'parentId' => 214,
                                                            'slug' => 'earrings',
                                                            'count' => 0,
                                                        ),
                                                    1 =>
                                                        array (
                                                            'name' => 'Necklaces',
                                                            'id' => 226,
                                                            'parentId' => 214,
                                                            'slug' => 'necklaces',
                                                            'count' => 0,
                                                        ),
                                                    2 =>
                                                        array (
                                                            'name' => 'Rings',
                                                            'id' => 227,
                                                            'parentId' => 214,
                                                            'slug' => 'rings',
                                                            'count' => 0,
                                                        ),
                                                    3 =>
                                                        array (
                                                            'name' => 'Bracelets',
                                                            'id' => 228,
                                                            'parentId' => 214,
                                                            'slug' => 'bracelets',
                                                            'count' => 0,
                                                        ),
                                                    4 =>
                                                        array (
                                                            'name' => 'Charms',
                                                            'id' => 230,
                                                            'parentId' => 214,
                                                            'slug' => 'charms',
                                                            'count' => 0,
                                                        ),
                                                    5 =>
                                                        array (
                                                            'name' => 'Money Clips',
                                                            'id' => 231,
                                                            'parentId' => 214,
                                                            'slug' => 'money-clips',
                                                            'count' => 0,
                                                        ),
                                                    6 =>
                                                        array (
                                                            'name' => 'Cufflinks',
                                                            'id' => 232,
                                                            'parentId' => 214,
                                                            'slug' => 'cufflinks',
                                                            'count' => 0,
                                                        ),
                                                    7 =>
                                                        array (
                                                            'name' => 'Gift Sets',
                                                            'id' => 233,
                                                            'parentId' => 214,
                                                            'slug' => 'gift-sets',
                                                            'count' => 0,
                                                        ),
                                                    8 =>
                                                        array (
                                                            'name' => 'Keychains',
                                                            'id' => 234,
                                                            'parentId' => 214,
                                                            'slug' => 'keychains',
                                                            'count' => 0,
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                    3 =>
                        array (
                            'name' => 'test1',
                            'id' => 251,
                            'parentId' => NULL,
                            'slug' => 'test1',
                            'count' => 1,
                        ),
                ),
        );

        return [
            $this->regionKey => [
                $this->materialKey => $data
            ]
        ];
    }
}
