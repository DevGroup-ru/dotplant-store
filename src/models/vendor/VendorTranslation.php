<?php

namespace DotPlant\Store\models\vendor;

use DevGroup\Entity\traits\EntityTrait;
use DevGroup\Entity\traits\SeoTrait;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%dotplant_store_vendor_translation}}".
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
 * @property string $announce
 * @property string $content
 */
class VendorTranslation extends ActiveRecord
{
    use EntityTrait;
    use SeoTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_vendor_translation}}';
    }

    protected $rules = [
        [['is_active'], 'integer'],
        [['announce', 'content'], 'string'],
        [['url'], 'string', 'max' => 800],
    ];

    /**
     * @inheritdoc
     */
    public function getAttributeLabels()
    {
        return [
            'url' => Yii::t('dotplant.store', 'Url'),
            'is_active' => Yii::t('dotplant.store', 'Is active'),
            'announce' => Yii::t('dotplant.store', 'Announce'),
            'content' => Yii::t('dotplant.store', 'Content'),
        ];
    }
}
