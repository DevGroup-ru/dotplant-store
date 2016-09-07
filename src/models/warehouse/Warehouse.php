<?php

namespace DotPlant\Store\models\warehouse;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\Store\interfaces\WarehouseInterface;
use DotPlant\Store\interfaces\WarehouseTypeInterface;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * This is the model class for table "{{%dotplant_store_warehouse}}".
 *
 * @todo: Add indexes for all queries
 *
 * @property integer $id
 * @property integer $type
 * @property integer $priority
 */
class Warehouse extends \yii\db\ActiveRecord implements WarehouseInterface
{
    use MultilingualTrait;
    use TagDependencyTrait;

    const TYPE_WAREHOUSE = 1;
    const TYPE_SELLER = 2;

    const STATUS_IN_STOCK = 1;
    const STATUS_BY_REQUEST = 2;
    const STATUS_OUT_OF_STOCK = 3;

    private static $_identityMap = [];

    private static $_typesMap = [
        self::TYPE_WAREHOUSE => TypeWarehouse::class,
        self::TYPE_SELLER => TypeSeller::class,
    ];

    public static function getTypes()
    {
        return [
            self::TYPE_WAREHOUSE => Yii::t('dotplant.store', 'Warehouse'),
            self::TYPE_SELLER => Yii::t('dotplant.store', 'Seller'),
        ];
    }

    /**
     * =================================================================================================================
     */

    private static function fillMap()
    {
        if (empty(static::$_identityMap)) {
            static::$_identityMap = static::find()
                ->indexBy('id')
                ->orderBy(['priority' => SORT_ASC])
                ->all();
        }
    }

    public static function getMap()
    {
        static::fillMap();
        return static::$_identityMap;
    }

    public static function getFromMap($id)
    {
        static::fillMap();
        return isset(static::$_identityMap[$id]) ? static::$_identityMap[$id] : null;
    }

    /**
     * @inheritdoc
     */
    public static function getWarehouse($goodsId, $warehouseId, $asArray = true)
    {
        $warehouse = static::getFromMap($warehouseId);
        $goodsWarehouse = GoodsWarehouse::find()
            ->where(
                [
                    'goods_id' => $goodsId,
                    'warehouse_id' => $warehouseId,
                ]
            )
            ->asArray(true)
            ->limit(1)
            ->one();
        return $asArray
            ? $goodsWarehouse
            : static::populateRecord(static::$_typesMap[$warehouse['type']], $goodsWarehouse);
    }

    /**
     * @inheritdoc
     */
    public static function getWarehouses($goodsId, $asArray = true, $allowedOnly = true)
    {
        $warehouses = static::getMap();
        $warehouseIds = array_keys($warehouses);
        $condition = ['goods_id' => $goodsId, 'warehouse_id' => $warehouseIds];
        if ($allowedOnly) {
            $condition['is_allowed'] = 1;
        }
        $goodsWarehouses = GoodsWarehouse::find()
            ->where($condition)
            ->indexBy('warehouse_id')
            ->orderBy(new Expression('FIELD(warehouse_id, ' . implode(', ', $warehouseIds) . ')'))
            ->asArray(true)
            ->all();
        if ($asArray) {
            return $goodsWarehouses;
        }
        foreach ($goodsWarehouses as $warehouseId => $goodsWarehouse) {
            // @todo: refactor it. Split all rows by types and populate it as batch
            if (isset(self::$_typesMap[$warehouses[$warehouseId]->type])) {
                $activeQuery = new ActiveQuery(self::$_typesMap[$warehouses[$warehouseId]->type]);
                $records = $activeQuery->populate([$goodsWarehouse]);
                $goodsWarehouses[$warehouseId] = $records[0];
            }
        }
        return $goodsWarehouses;
    }

    /**
     * @inheritdoc
     */
    public static function isAvailable($goodsId)
    {
        return static::getStatusCode($goodsId) !== self::STATUS_OUT_OF_STOCK;
    }

    /**
     * @inheritdoc
     */
    public static function getStatusCode($goodsId)
    {
        $row = GoodsWarehouse::find()
            ->select(['is_unlimited', 'available_count'])
            ->where(['goods_id' => $goodsId, 'is_allowed' => 1])
            ->orderBy(['is_unlimited' => SORT_DESC, 'available_count' => SORT_DESC])
            ->limit(1)
            ->asArray(true)
            ->one();
        if ($row === null) {
            return self::STATUS_OUT_OF_STOCK;
        }
        return $row['is_unlimited'] == 0 || $row['available_count'] > 0
            ? self::STATUS_IN_STOCK
            : self::STATUS_BY_REQUEST;
    }

    /**
     * @inheritdoc
     */
    public static function getMinPrice($goodsId, $isRetailPrice = true)
    {
        $priceField = $isRetailPrice ? 'retail_price' : 'wholesale_price';
        return GoodsWarehouse::find()
            ->select([new Expression('MIN(`' . $priceField . '`) AS `price`'), 'currency_iso_code'])
            ->where(['goods_id' => $goodsId, 'is_allowed' => 1])
            ->groupBy('currency_iso_code')
            ->orderBy([$priceField => SORT_ASC])
            ->asArray(true)
            ->all();
    }

    /**
     * =================================================================================================================
     */

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationModelClass' => WarehouseTranslation::class,
                'translationPublishedAttribute' => false,
            ],
            'cacheable' => [
                'class' => CacheableActiveRecord::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_warehouse}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['priority', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'type' => Yii::t('dotplant.store', 'Type'),
            'priority' => Yii::t('dotplant.store', 'Priority'),
        ];
    }

    public function search($params)
    {
        $query = static::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [],
            ]
        );
        return $dataProvider;
    }
}
