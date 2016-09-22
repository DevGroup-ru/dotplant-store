<?php


namespace DotPlant\Store\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class ExtendedPriceAssets extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@DotPlant/Store/assets/dist';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/backend-extended-price.js',
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        JqueryAsset::class,
    ];
}
