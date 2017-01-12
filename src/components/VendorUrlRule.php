<?php

namespace DotPlant\Store\components;

use DotPlant\Store\models\vendor\Vendor;
use yii\base\Object;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRuleInterface;

class VendorUrlRule extends Object implements UrlRuleInterface
{

    /**
     * Parses the given request and returns the corresponding route and parameters.
     *
     * @param UrlManager $manager the URL manager
     * @param Request $request the request component
     *
     * @return array|bool the parsing result. The route and the parameters are returned as an array.
     * If false, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        $parts = explode('/', preg_replace('#/+#', '/', $pathInfo));
        if (count($parts) === 1) {
            $slug = reset($parts);
            $translationTableName = Vendor::getTranslationTableName();
            $vendor = Vendor::find()->where( // @todo: Use SQL-index
                [
                    'and',
                    [$translationTableName . '.slug' => $slug],
                    [$translationTableName . '.is_active' => true],
                    ['is_deleted' => false],
                ]
            )->asArray(true)->limit(1)->one();
            if ($vendor !== null) {
                return [
                    'store/vendor/show',
                    ['entities' => [Vendor::class => [$vendor['id']]]],
                ];
            }

        }
        return false;
    }

    /**
     * Creates a URL according to the given route and parameters.
     *
     * @param UrlManager $manager the URL manager
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     *
     * @return string|bool the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params)
    {
        // TODO: Implement createUrl() method.
    }
}
