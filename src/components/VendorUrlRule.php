<?php

namespace DotPlant\Store\components;

use DevGroup\TagDependencyHelper\LazyCacheTrait;
use DevGroup\TagDependencyHelper\NamingHelper;
use DotPlant\Store\models\vendor\Vendor;
use DotPlant\Store\models\vendor\VendorTranslation;
use Yii;
use yii\base\Object;
use yii\caching\Cache;
use yii\caching\TagDependency;
use yii\db\Query;
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
        if (
            $route !== 'store/vendor/show'
            || !isset($params['entities'][Vendor::class])
            || count($params['entities'][Vendor::class]) !== 1
        ) {
            return false;
        }
        $languageId = isset($params['languageId']) ? $params['languageId'] : Yii::$app->multilingual->language_id;
        /** @var Cache|LazyCacheTrait $lazyCache */
        $lazyCache = Yii::$app->cache;
        $vendorId = $params['entities'][Vendor::class];

        $vendor = $lazyCache->lazy(
            function() use($vendorId, $languageId) {
                return (new Query()) // it's released via Query to prevent auto-attaching of language id
                ->select(['slug', 'id'])
                    ->from(Vendor::tableName())
                    ->where(['id' => $vendorId, 'language_id' => $languageId])
                    ->innerJoin(VendorTranslation::tableName(), 'id = model_id')
                    ->one();
            },
            "createUrl:VendorRow:$languageId:$vendorId",
            86400,
            new TagDependency([
                'tags' => [
                    NamingHelper::getObjectTag(Vendor::class, $vendorId)
                ]
            ])
        );

        if ($vendor === false) {
            return false;
        }

        return $vendor['slug'];
    }
}
