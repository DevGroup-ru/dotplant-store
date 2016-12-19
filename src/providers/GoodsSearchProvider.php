<?php

namespace DotPlant\Store\providers;

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
     * @var string the sort get parameter
     */
    public $sortParameter = 'sort';

    /**
     * @var array sort fields
     */
    public $sortFields = ['retail_price', 'id'];


    public function pack()
    {
        return [
            'class' => static::class,
            'entities' => $this->entities,
        ];
    }

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

        foreach ($good->propertiesValues as $property_id => $property_value) {
            if (isset($property_value[$languageId])) {
                $properties[$good->propertiesAttributes[$property_id]] = $property_value[$languageId][0];
            } else {
                $properties[$good->propertiesAttributes[$property_id]] = $property_value;
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

    private function getSort()
    {
        $attributes = [];

        foreach ($this->sortFields as $sort_field) {
            $attributes[$sort_field] = [
                'asc' => [$sort_field => SORT_ASC],
                'desc' => [$sort_field => SORT_DESC],
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

    private function getGoodsIds($query)
    {
        return $query->column();
    }

    private function getTree($full_section_array, &$sections)
    {
        foreach ($sections as $key => $section) {
            if (array_key_exists($section['id'], $full_section_array)) {
                $sections[$key]['subsections'] = $full_section_array[$section['id']];
                $this->getTree($full_section_array, $sections[$key]['subsections']);
            }
        }
        return $sections;
    }

    private function getGoodsSectionsTree($goods_ids)
    {
        $category_goods_models = CategoryGoods::find()
            ->select('structure_id')
            ->addSelect('goods_id')
            ->where(
                [
                    'goods_id' => $goods_ids
                ]
            )
            ->all();

        $category_goods = [];
        foreach ($category_goods_models as $section) {
            $category_goods[$section->structure_id][] = $section->goods_id;
        }

        $goods_category_models = BaseStructure::find()
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

        $goods_category = [];

        foreach ($goods_category_models as $section) {
            $goods_category[$section->parent_id][] = [
                'name' => $section->defaultTranslation->name,
                'id' => $section->id,
                'parentId' => $section->parent_id,
                'slug' => $section->defaultTranslation->slug,
                'count' => isset($category_goods[$section->id]) ? count($category_goods[$section->id]) : 0
            ];
        }

        $first_category = reset($goods_category);

        return $this->getTree($goods_category, $first_category);
    }

    private function getParentId()
    {
        $get = \Yii::$app->request->get();

        return (
        (!empty($get[$this->parentIdParameter]) && intval($get[$this->parentIdParameter]) > 0)
            ? intval($get[$this->parentIdParameter])
            : $this->parentId
        );
    }

    public function getEntities(&$actionData)
    {
        $get = \Yii::$app->request->get();

        if (isset($get[$this->searchParameter])) {
            $search_phrase = $get[$this->searchParameter];

            $query = Goods::find()
                ->distinct()
                ->innerJoin(
                    '{{%dotplant_store_goods_eav}}',
                    '{{%dotplant_store_goods_eav}}.[[model_id]] = ' . Goods::tableName() . '.[[id]]'
                )
                ->where(
                    [
                        'like', '[[name]]', $search_phrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[description]]', $search_phrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[announce]]', $search_phrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[value_string]]', $search_phrase
                    ]
                )
                ->orWhere(
                    [
                        'like', '[[value_text]]', $search_phrase
                    ]
                )
                ->andWhere(
                    [
                        'is_active' => 1,
                        'is_deleted' => 0
                    ]
                );

            $countQuery = clone $query;

            $parent_id = $this->getParentId();

            if ($parent_id !== false) {
                $query->innerJoin(
                    CategoryGoods::tableName(),
                    CategoryGoods::tableName() . '.[[goods_id]] = ' . Goods::tableName() . '.[[id]]'
                )
                    ->andWhere(
                        [
                            'structure_id' => $parent_id
                        ]
                    );
            }

            $pagerQuery = clone $query;

            $goods_ids = $this->getGoodsIds($countQuery);
            $goods_sections_tree = $this->getGoodsSectionsTree($goods_ids);

            $limit = (!empty($get[$this->limitParameter]) ? intval($get[$this->limitParameter]) : false);

            $total_count = $pagerQuery->count();
            $pages = new Pagination(
                [
                    'totalCount' => $total_count,
                    'pageParam' => $this->paginationParameter,
                    'defaultPageSize' => $limit,
                    'pageSizeLimit' => [$this->minLimit, $this->maxLimit]
                ]
            );

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

            $goods_data = [];
            if (!empty($goods)) {
                PropertiesHelper::fillProperties($goods);
                $goods_data = array_map(array($this, 'buildGoods'), $goods);
            }
        }

        $data = [
            $this->blockKey => isset($goods_data) ? $goods_data : [],
            $this->totalCountKey => isset($total_count) ? $total_count : 0,
            $this->paginationKey => isset($pagination) ? $pagination : [],
            $this->categoriesKey => isset($goods_sections_tree) ? $goods_sections_tree : []
        ];

        return [
            $this->regionKey => [
                $this->materialKey => $data
            ]
        ];
    }
}