<?php


namespace DotPlant\Store\widgets\backend;


use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\base\Widget;
use yii\db\ActiveRecord;

class ExtendedPriceEditWidget extends Widget
{
    /**
     * @var ActiveRecord
     */
    public $model;

    function run()
    {
        if ($this->model->isNewRecord) {
            return '';
        }
        $allRules = ExtendedPriceRule::find()->with('extended-price')->where(
            [ExtendedPrice::tableName() . '.target_class' => $this->model->className()]
        )->all();
        $allRules = array_reduce(
            $allRules,
            function ($carry, ExtendedPriceRule $item) {

            },
            []
        );
        return $this->render('tesssss');
    }
}