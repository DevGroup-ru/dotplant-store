<?php


namespace DotPlant\Store\handlers\extendedPrice;


use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use DotPlant\Store\models\order\Promocode;
use yii\widgets\ActiveForm;

class PromocodeRule extends AbstractRule
{
    public $promocodes = [];

    public function rules()
    {
        return [
            ['promocodes', 'each', 'rule' => ['integer']],
            [
                'promocodes',
                'each',
                'rule' => ['exist', 'targetClass' => Promocode::class, 'targetAttribute' => 'id'],
            ],
        ];
    }

    private static $_allPromocodes = [];

    public static function check($object, $params = [])
    {
        // @todo think how to get promocode from user here
        $result = false;
        if (empty($params['promocodes']) === false) {
            $result = in_array($object->id, $params['promocodes']);
        }
        return $result;
    }

    /**
     * @param ActiveForm $form
     * @param ExtendedPriceRule $rule
     *
     * @return mixed
     */
    public static function renderForm(ActiveForm $form, ExtendedPriceRule $rule)
    {

        if (self::$_allPromocodes === []) {
            self::$_allPromocodes = Promocode::find()->indexBy('id')->select('name')->column();
        }
        return \Yii::$app->view->render(
            '@DotPlant/Store/handlers/extendedPrice/views/promocode_rule',
            [
                'model' => new PromocodeRule($rule->params),
                'rule' => $rule,
                'form' => $form,
                'promocodes' => self::$_allPromocodes,
            ]
        );
    }
}