<?php

namespace DotPlant\Store\components;

use DevGroup\Multilingual\behaviors\MultilingualActiveRecord;
use DevGroup\Multilingual\traits\MultilingualTrait;
use yii\db\ActiveRecord;
use yii\db\Query;

class MultilingualListDataQuery extends Query
{
    /**
     * MultilingualListDataQuery constructor
     * @param ActiveRecord|MultilingualActiveRecord|MultilingualTrait|string $modelClassName
     * @param string $valueAttribute
     * @param string $idAttribute
     * @param string $languageAttribute
     */
    public function __construct(
        $modelClassName,
        $valueAttribute = 'name',
        $idAttribute = 'id',
        $languageAttribute = 'language_id'
    ) {
        $config = [
            'select' => [$valueAttribute, $idAttribute],
            'from' => [$modelClassName::tableName()],
            'join' => [
                ['INNER JOIN', $modelClassName::getTranslationTableName(), 'id = model_id'],
            ],
            'groupBy' => ['model_id'],
            'orderBy' => [
                new SortByLanguageExpression(),
                'sort_order' => SORT_ASC,
            ],
            'indexBy' => $idAttribute,
        ];
        parent::__construct($config);
    }
}
