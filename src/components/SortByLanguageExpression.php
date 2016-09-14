<?php

namespace DotPlant\Store\components;

use DevGroup\Multilingual\models\Language;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class SortByLanguageExpression extends Expression
{
    private static $_languageIds = [];

    public function __construct($fieldName = 'language_id')
    {
        $lang = \Yii::$app->language;
        if (!isset(self::$_languageIds[$lang])) {
            self::$_languageIds[$lang] = ArrayHelper::getColumn(Language::findAll(['iso_639_1' => $lang]), 'id');
            if (count(self::$_languageIds[$lang]) === 0) {
                self::$_languageIds[$lang] = [0]; // It's a dummy if language not found
            }
        }
        $expression = 'FIELD(`' . $fieldName . '`, ' . implode(', ', self::$_languageIds[$lang]) . ')';
        parent::__construct($expression, [], []);
    }
}
