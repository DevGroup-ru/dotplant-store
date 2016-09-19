<?php

namespace DotPlant\Store\handlers\extendedPrice;

use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\EntityStructure\models\StructureTranslation;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\db\Query;
use yii\widgets\ActiveForm;

class StructureRule extends AbstractRule
{
    public $structures = [];

    private static $_allStructures = [];

    public function rules()
    {
        return [
            ['structure', 'each', 'rule' => ['integer']]
        ];
    }

    public static function check($object, $params = [])
    {
        $result = false;
        if (empty($params['structures']) === false) {
            $result = (new Query())->from('{{%dotplant_store_goods_category%}}')
                ->where(['goods_id' => $object->id, 'structure_id' => $params['structures']])
                ->exists();
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

        if (self::$_allStructures === []) {
            self::$_allStructures = BaseStructure::find()
                ->indexBy('id')
                ->select([StructureTranslation::tableName() . '.name', BaseStructure::tableName() . '.id'])
                ->with(['translations'])
                ->column();
        }
        return \Yii::$app->view->render(
            '@DotPlant/Store/handlers/extendedPrice/views/structure_rule',
            [
                'model' => new StructureRule($rule->params),
                'rule' => $rule,
                'form' => $form,
                'structures' => self::$_allStructures
            ]
        );
    }
}
