<?php

namespace DotPlant\Store\helpers;

use DevGroup\Multilingual\models\Context;

/**
 * Class BackendHelper
 * @package DotPlant\Store\helpers
 */
class BackendHelper
{
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
}
