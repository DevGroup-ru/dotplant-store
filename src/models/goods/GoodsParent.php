<?php

namespace DotPlant\Store\models\goods;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_goods_parent}}".
 *
 * @property integer $goods_id
 * @property integer $goods_parent_id
 * @property integer $sort_order
 */
class GoodsParent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_goods_parent}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_id', 'goods_parent_id'], 'required'],
            ['goods_id', 'compare', 'compareAttribute' => 'goods_parent_id', 'operator' => '!='],
            [['goods_id', 'goods_parent_id', 'sort_order'], 'integer'],
            [
                ['goods_parent_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Goods::class,
                'targetAttribute' => ['goods_parent_id' => 'id']
            ],
            [
                ['goods_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Goods::class,
                'targetAttribute' => ['goods_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'goods_id' => Yii::t('dotplant.store', 'Goods ID'),
            'goods_parent_id' => Yii::t('dotplant.store', 'Goods Parent ID'),
            'sort_order' => Yii::t('dotplant.store', 'Sort Order'),
        ];
    }
}
