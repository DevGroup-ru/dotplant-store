<?php

namespace DotPlant\Store;

class Module extends \yii\base\Module
{
    /**
     * @return self Module instance in application
     */
    public static function module()
    {
        $module = \Yii::$app->getModule('store');
        if ($module === null) {
            $module = \Yii::createObject(self::class, ['store']);
        }
        return $module;
    }
}
