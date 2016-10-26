<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\BaseActionsInfoTrait;
use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SoftDeleteTrait;
use devgroup\JsTreeWidget\helpers\ContextMenuHelper;
use DotPlant\EntityStructure\actions\BaseEntityTreeAction;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\Monster\Universal\MonsterEntityTrait;
use DotPlant\Store\actions\goods\GoodsAutocompleteAction;
use DotPlant\Store\actions\goods\GoodsDeleteAction;
use DotPlant\Store\actions\goods\GoodsListAction;
use DotPlant\Store\actions\goods\GoodsManageAction;
use DotPlant\Store\actions\goods\GoodsRestoreAction;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class GoodsCategory
 *
 * @package DotPlant\Store
 */
class GoodsCategory extends BaseStructure
{
    use EntityTrait;
    use BaseActionsInfoTrait;
    use SoftDeleteTrait;
    use MonsterEntityTrait;

    const TRANSLATION_CATEGORY = 'dotplant.store';

    protected static $tablePrefix = 'dotplant_store_category';

    protected static function getPageSize()
    {
        //todo place it to the Module
        return 15;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'multilingual' => [
                    'translationModelClass' => GoodsCategoryTranslation::class,
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getAccessRules()
    {
        return [//TODO
        ];
    }

    /**
     * @inheritdoc
     */
    public static function jsTreeContextMenuActions()
    {
        $categoryEntityId = Entity::getEntityIdForClass(self::class);
        return [
            'products' => [
                'label' => Yii::t('dotplant.store', 'Show Products'),
                'action' => ContextMenuHelper::actionUrl(['/structure/entity-manage/products']),
                'showWhen' => ['entity_id' => $categoryEntityId],
            ],
            'addProduct' => [
                'label' => Yii::t('dotplant.store', 'Add product'),
                'action' => ContextMenuHelper::actionUrl(['/structure/entity-manage/goods-manage']),
                'showWhen' => ['entity_id' => $categoryEntityId],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static $injectionActions = [
        'products' => [
            'class' => GoodsListAction::class,
        ],
        'goods-manage' => [
            'class' => GoodsManageAction::class,
        ],
        'goods-delete' => [
            'class' => GoodsDeleteAction::class,
        ],
        'goods-restore' => [
            'class' => GoodsRestoreAction::class,
        ],
        'goods-autocomplete' => [
            'class' => GoodsAutocompleteAction::class,
        ],
        'category-tree' => [
            'class' => BaseEntityTreeAction::class,
            'className' => GoodsCategory::class,
            'cacheKey' => 'GoodsManageCatTree',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function getEditPageTitle()
    {
        return (true === $this->getIsNewRecord()) ? Yii::t('dotplant.store', 'New goods category') : Yii::t(
            'dotplant.store',
            'Edit {title}',
            ['title' => $this->name]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getModuleBreadCrumbs()
    {
        return [
            [
                'url' => ['/structure/entity-manage/index'],
                'label' => Yii::t('dotplant.store', 'Goods category management'),
            ],
        ];
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function advancedTranslatableAttributes()
    {
        $result = Yii::$app->cache->get(__CLASS__.':'.__FUNCTION__);
        if ($result === false) {
            $result = array_keys(GoodsCategoryExtended::getTableSchema()->columns);
            $result[] = 'content';
            $result[] = 'providers';
            Yii::$app->cache->set(__CLASS__.':'.__FUNCTION__, $result, 86400);
        }
        return $result;
    }
}
