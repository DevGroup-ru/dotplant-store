<?php
namespace DotPlant\Store\handlers\extendedPrice;

use DevGroup\Users\models\User;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\widgets\ActiveForm;

class UserRule extends AbstractRule
{
    public $users = [];

    public function rules()
    {
        return [
            ['users', 'each', 'rule' => ['integer']]
        ];
    }

    public static function check($object, $params = [])
    {
        $result = false;
        if (empty($params['users']) === false) {
            $result = in_array(\Yii::$app->user->id, $params['users']);
        }
        return $result;
    }


    public static function renderForm(ActiveForm $form, ExtendedPriceRule $rule)
    {
        $users = User::find()
            ->indexBy('id')
            ->select(['username', 'id'])
            ->column();

        return \Yii::$app->view->render(
            '@DotPlant/Store/handlers/extendedPrice/views/user_rule',
            ['model' => new UserRule($rule->params), 'rule' => $rule, 'form' => $form, 'users' => $users]
        );
    }
}
