<?php

namespace DotPlant\Store\models;

use DevGroup\ExtensionsManager\models\BaseConfigurationModel;
use DotPlant\Store\Module;

class Configuration extends BaseConfigurationModel
{
    /**
     * @inheritdoc
     */
    public function getModuleClassName()
    {
        return Module::class;
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function webApplicationAttributes()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function consoleApplicationAttributes()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function commonApplicationAttributes()
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'dotplant.store' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'messages',
                        ],
                    ],
                ],
            ],
            'modules' => [
                'store' => [
                    'class' => Module::class,
                    'layout' => '@app/views/layouts/admin',
                ],
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public function appParams()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function aliases()
    {
        return [
            '@DotPlant/Store' => realpath(dirname(__DIR__)),
        ];
    }
}
