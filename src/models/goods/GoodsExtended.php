<?php

namespace DotPlant\Store\models\goods;

use DevGroup\DataStructure\behaviors\PackedJsonAttributes;
use yii\db\ActiveRecord;

/**
 * Class GoodsCategoryExtended
 * @package DotPlant\Store\models\goods
 */
class GoodsExtended extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_goods_ext}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'PackedJsonAttributes' => [
                'class' => PackedJsonAttributes::class,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'packed_json_content',
                    'packed_json_providers',
                ],
                'default',
                'value' => '[]',
            ]
        ];
    }
}
