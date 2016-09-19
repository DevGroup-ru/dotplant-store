<?php

namespace DotPlant\Store\interfaces;

use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\widgets\ActiveForm;

/**
 * Interface ExtendedPriceHandlerInterface
 * @package DotPlant\Store\interfaces
 */
interface ExtendedPriceHandlerInterface
{
    /**
     * @param $object
     * @param array $params
     * @return boolean
     */
    public static function check($object, $params = []);

    /**
     * @param ActiveForm $form
     * @param ExtendedPriceRule $rule
     * @return mixed
     */
    public static function renderForm(ActiveForm $form, ExtendedPriceRule $rule);
}
