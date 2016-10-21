<?php

namespace DotPlant\Store\models\goods;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use yii\db\ActiveRecord;
use Yii;
use yii\helpers\ArrayHelper;
use yii2tech\ar\role\RoleBehavior;

/**
 * This is the model class for table "{{%dotplant_goods_translation}}".
 *
 * @property integer $model_id
 * @property integer $language_id
 * @property string $name
 * @property string $title
 * @property string $h1
 * @property string $breadcrumbs_label
 * @property string $meta_description
 * @property string $slug
 * @property string $url
 * @property integer $is_active
 * @property string $description
 */
class GoodsTranslation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_goods_translation}}';
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'slug', 'name'], 'required'],
            [['is_active'], 'integer'],
            [['announce', 'description'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['url'], 'string', 'max' => 800],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAttributeLabels()
    {
        return [
            'name' => Yii::t('dotplant.store', 'Name'),
            'url' => Yii::t('dotplant.store', 'Url'),
            'is_active' => Yii::t('dotplant.store', 'Is active'),
            'announce' => Yii::t('dotplant.store', 'Announce'),
            'description' => Yii::t('dotplant.store', 'Description'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'roleBehavior' => [
                    'class' => RoleBehavior::className(),
                    'roleRelation' => 'extended',
                ],
            ]
        );
    }

    /**
     * Modifies base query to include extended relation to reduce total queries count
     * @return \yii\db\ActiveQuery
     */
    public static function find()
    {
        $query = parent::find();
        $query->joinWith('extended');
        return $query;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExtended()
    {
        return $this->hasOne(GoodsExtended::className(), ['model_id' => 'model_id', 'language_id' => 'language_id']);
    }
}
