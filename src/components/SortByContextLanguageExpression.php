<?php

namespace DotPlant\Store\components;

use yii\db\Expression;
use yii\helpers\ArrayHelper;

class SortByContextLanguageExpression extends Expression
{
    private static $_contextLanguageIds = [];

    public function __construct($contextId, $fieldName = 'language_id')
    {
        if (!isset(self::$_contextLanguageIds[$contextId])) {
            self::$_contextLanguageIds[$contextId] = ArrayHelper::getColumn(
                call_user_func([\Yii::$app->multilingual->modelsMap['Language'], 'findAll'], ['context_id' => $contextId]),
                'id'
            );
            if (count(self::$_contextLanguageIds[$contextId]) === 0) {
                self::$_contextLanguageIds[$contextId] = [0]; // It's a dummy if language not found
            }
        }
        $expression = 'FIELD(`' . $fieldName . '`, ' . implode(', ', self::$_contextLanguageIds[$contextId]) . ')';
        parent::__construct($expression, [], []);
    }
}
