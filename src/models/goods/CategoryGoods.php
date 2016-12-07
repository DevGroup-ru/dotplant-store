<?php

namespace DotPlant\Store\models\goods;

use DevGroup\AdminUtils\traits\FetchModels;
use DevGroup\TagDependencyHelper\CacheableActiveRecord;
use DevGroup\TagDependencyHelper\NamingHelper;
use DevGroup\TagDependencyHelper\TagDependencyTrait;
use DotPlant\EntityStructure\models\BaseStructure;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%dotplant_store_category_goods}}".
 *
 * @property integer $structure_id
 * @property integer $goods_id
 * @property integer $sort_order
 */
class CategoryGoods extends ActiveRecord
{
    use TagDependencyTrait;
    use FetchModels;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
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
        return '{{%dotplant_store_category_goods}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['structure_id', 'goods_id'], 'required'],
            [['structure_id', 'goods_id', 'sort_order'], 'integer'],
            [
                ['goods_id', 'structure_id'],
                'unique',
                'targetAttribute' => ['goods_id', 'structure_id'],
                'message' => 'The combination of Structure ID and Goods ID has already been taken.'
            ],
            [
                ['goods_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Goods::class,
                'targetAttribute' => ['goods_id' => 'id']
            ],
            [
                ['structure_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => BaseStructure::class,
                'targetAttribute' => ['structure_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'structure_id' => Yii::t('dotplant.store', 'Structure ID'),
            'goods_id' => Yii::t('dotplant.store', 'Goods ID'),
            'sort_order' => Yii::t('dotplant.store', 'Sort order'),
        ];
    }

    /**
     * @param int $goodsId
     * @param array $categories
     * @throws \yii\db\Exception
     */
    public static function saveBindings($goodsId, array $categories)
    {
        if (false === empty($categories)) {
            self::deleteAll(['goods_id' => $goodsId]);
            $categories = GoodsCategory::find()->select('id')->where(['id' => $categories])->column();
            $batch = [];
            foreach ($categories as $id) {
                $batch[] = [$goodsId, $id];
            }
            self::getDb()->createCommand()->batchInsert(
                self::tableName(),
                ['goods_id', 'structure_id'],
                $batch
            )->execute();

            TagDependency::invalidate(
                Yii::$app->cache,
                [NamingHelper::getCommonTag(self::class)]
            );
        }
    }

    /**
     * @param int $goodsId
     * @param null $contextId
     *
     * @return array
     */
    public static function getBindings($goodsId, $contextId = null)
    {
        $cacheKey = implode(':', ['GoodsCategoriesBindings', $goodsId, intval($contextId)]);
        $list = Yii::$app->cache->get($cacheKey);
        if (false === $list) {
            $listQuery = self::find()->select('structure_id')->where(['goods_id' => $goodsId])->orderBy(
                [self::tableName() . '.sort_order' => SORT_DESC]
            );
            if ($contextId !== null) {
                $listQuery->leftJoin(
                    BaseStructure::tableName(),
                    self::tableName() . '.structure_id=' . BaseStructure::tableName() . '.id'
                )->andWhere(
                    [BaseStructure::tableName() . '.context_id' => $contextId]
                );
            }
            $listQuery->createCommand()->getRawSql();
            $list = $listQuery->column();
            if (false === empty($list)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $list,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                NamingHelper::getCommonTag(self::class),
                            ],
                        ]
                    )
                );
            }
        }
        return (false === $list) ? [] : $list;
    }
}
