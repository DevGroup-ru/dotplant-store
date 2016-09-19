<?php

namespace DotPlant\Store\handlers\extendedPrice;

use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\widgets\ActiveForm;
use Yii;

class UserGroupRule extends AbstractRule
{
    public $role;

    public function rules()
    {
        return [
            ['role', 'string']
        ];
    }

    public static function check($object, $params = [])
    {
        $result = false;
        if (empty($params['role']) === false) {
            $result = Yii::$app->user->can($params['role']);
        }
        return $result;
    }


    public static function renderForm(ActiveForm $form, ExtendedPriceRule $rule)
    {
        $roles = [];
        $authManager = Yii::$app->getAuthManager();
        foreach ($authManager->getRoles() as $name => $role) {
            $roles[$name] = $name . (empty($role->description) ? '' : " [{$role->description}] ");
        }

        return \Yii::$app->view->render(
            '@DotPlant/Store/handlers/extendedPrice/views/user_group',
            ['model' => new UserGroupRule($rule->params), 'rule' => $rule, 'form' => $form, 'roles' => $roles]
        );
    }
}
