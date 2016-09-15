<?php

namespace DotPlant\Store\models\order;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SoftDeleteTrait;
use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use DotPlant\Store\components\MultilingualListDataQuery;
use DotPlant\Store\components\SortByLanguageExpression;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%dotplant_store_order_status}}".
 *
 * @property integer $id
 * @property integer $context_id
 * @property string $label_class
 * @property integer $is_active
 * @property integer $sort_order
 * @property integer $is_deleted
 */
class OrderStatus extends \yii\db\ActiveRecord
{
    use EntityTrait;
    use MultilingualTrait;
    use SoftDeleteTrait;

    public function behaviors()
    {
        return [
            'multilingual' => [
                'class' => MultilingualActiveRecord::class,
                'translationPublishedAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_order_status}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['context_id'], 'required'],
            [['context_id', 'is_active', 'sort_order', 'is_deleted'], 'integer'],
            [['label_class'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'context_id' => Yii::t('dotplant.store', 'Context'),
            'label_class' => Yii::t('dotplant.store', 'Label class'),
            'is_active' => Yii::t('dotplant.store', 'Is active'),
            'sort_order' => Yii::t('dotplant.store', 'Sort order'),
            'is_deleted' => Yii::t('dotplant.store', 'Is deleted'),
        ];
    }

    /**
     * Get list data for dropdown
     * @param $contextId int|null
     * @return string[]
     */
    public static function listData($contextId = null)
    {
        $condition = $contextId === null ? ['is_active' => 1] : ['context_id' => [0, $contextId], 'is_active' => 1];
        return (new MultilingualListDataQuery(static::class, 'label'))
            ->where($condition)
            ->column();
    }

    /**
     * Get order status by id with language priority
     * @param $id int
     * @return array|bool
     */
    public static function multilingualFindById($id)
    {
        $status = (new MultilingualListDataQuery(static::class))->select('*')
            ->where(['id' => $id])
            ->one();
        return $status;
    }

    /**
     * Get a translation relation with language priority
     * @return ActiveQuery $this
     */
    public function getSmartTranslation()
    {
        /** @var \yii\db\ActiveRecord|\DevGroup\Multilingual\behaviors\MultilingualActiveRecord $this */
        return $this->hasOne($this->getTranslationModelClassName(), ['model_id' => 'id'])
            ->groupBy('model_id')
            ->orderBy(new SortByLanguageExpression());
    }
}
