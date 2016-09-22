<?php

namespace DotPlant\Store\helpers;

use DevGroup\Multilingual\models\Context;
use DevGroup\TagDependencyHelper\NamingHelper;
use DevGroup\Users\helpers\ModelMapHelper;
use yii\caching\TagDependency;
use yii\db\Expression;

/**
 * Class BackendHelper
 * @package DotPlant\Store\helpers
 */
class BackendHelper
{
    const MANAGERS_LIST = 'dotplant.store.managers-list';
    /**
     * Get context id
     * @param int|null $contextId
     * @return int|null
     */
    public static function getContext($contextId = null)
    {
        if ($contextId !== null) {
            return $contextId;
        }
        $context = Context::find()->one();
        return $context !== null ? $context->id : null;
    }

    /**
     * Get dropdown list of managers
     * @return string[]
     */
    public static function managersDropDownList()
    {
        $result = \Yii::$app->cache->get(self::MANAGERS_LIST);
        if ($result === false) {
            $userIds = \Yii::$app->authManager->getUserIdsByRole('OrderManager');
            $result = call_user_func([ModelMapHelper::User()['class'], 'find'])
                ->select(
                    [
                        new Expression("CONCAT(`username`, ' (', `email`, ')')"),
                        'id',
                    ]
                )
                ->where(['id' => $userIds])
                ->indexBy('id')
                ->orderBy(['username' => SORT_ASC])
                ->column();
            \Yii::$app->cache->set(
                self::MANAGERS_LIST,
                $result,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            NamingHelper::getCommonTag(ModelMapHelper::User()['class']),
                        ],
                    ]
                )
            );
        }
        return $result;
    }
}
