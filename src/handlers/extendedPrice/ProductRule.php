<?php

namespace DotPlant\Store\handlers\extendedPrice;

use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;
use yii\widgets\ActiveForm;

class ProductRule extends AbstractRule
{
    public $products = [];

    public function rules()
    {
        return [
            ['products', 'each', 'rule' => ['integer']],
            [
                'products',
                'each',
                'rule' => ['exist', 'targetClass' => Goods::class, 'targetAttribute' => 'id']
            ],
        ];
    }

    private static $_allProducts = [];

    public static function check($object, $params = [])
    {
        $result = false;
        if (empty($params['products']) === false) {
            $result = in_array($object->id, $params['products']);
        }
        return $result;
    }

    /**
     * @param ActiveForm $form
     * @param ExtendedPriceRule $rule
     * @return mixed
     */
    public static function renderForm(ActiveForm $form, ExtendedPriceRule $rule)
    {

        if (self::$_allProducts === []) {
            self::$_allProducts = Goods::find()
                ->indexBy('id')
                ->select([GoodsTranslation::tableName() . '.name', Goods::tableName() . '.id'])
                ->with(['translations'])
                ->column();
        }
        return \Yii::$app->view->render(
            '@DotPlant/Store/handlers/extendedPrice/views/product_rule',
            ['model' => new ProductRule($rule->params), 'rule' => $rule, 'form' => $form, 'products' => self::$_allProducts]
        );
    }
}
