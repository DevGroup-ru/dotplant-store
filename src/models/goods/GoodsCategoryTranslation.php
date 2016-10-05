<?php

namespace DotPlant\Store\models\goods;

use DotPlant\EntityStructure\models\StructureTranslation;
use yii\helpers\ArrayHelper;
use yii2tech\ar\role\RoleBehavior;

/**
 * Class GoodsCategoryTranslation
 * @package DotPlant\Store\models\goods
 */
class GoodsCategoryTranslation extends StructureTranslation
{
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
        return $this->hasOne(GoodsCategoryExtended::className(), ['model_id' => 'model_id', 'language_id' => 'language_id']);
    }
}
