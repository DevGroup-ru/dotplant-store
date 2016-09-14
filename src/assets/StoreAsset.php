<?php

namespace DotPlant\Store\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

/**
 * Class StoreAsset
 * @package DotPlant\Store\assets
 */
class StoreAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@DotPlant/Store/assets/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/backend-goods.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JqueryAsset::class,
    ];
}