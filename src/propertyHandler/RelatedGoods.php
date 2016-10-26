<?php

namespace DotPlant\Store\propertyHandler;

use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\propertyHandler\AbstractPropertyHandler;
use DevGroup\DataStructure\propertyStorage\EAV;
use DotPlant\Store\models\goods\Goods;

class RelatedGoods extends AbstractPropertyHandler
{
    /** @inheritdoc */
    public static $multipleMode = Property::MODE_ALLOW_MULTIPLE;

    /** @inheritdoc */
    public static $allowInSearch = true;

    /** @inheritdoc */
    public static $allowedStorage = [
        EAV::class,
    ];

    /** @inheritdoc */
    public static $allowedTypes = [
        Property::DATA_TYPE_INTEGER
    ];

    /**
     * @inheritdoc
     */
    public function getValidationRules(Property $property)
    {
        $key = $property->key;
        return [
            [$key, 'each', 'skipOnEmpty' => true, 'rule' => ['filter', 'filter' => 'intval']]
        ];
    }

    /**
     * Render a property.
     * @param ActiveRecord | HasProperties | PropertiesTrait $model
     * @param Property $property
     * @param string $view
     * @param null | ActiveForm $form
     * @return string
     */
    public function renderProperty($model, $property, $view, $form = null)
    {
        $data = Goods::find()
            ->select(['dotplant_store_goods_translation.name', 'dotplant_store_goods.id'])
            ->indexBy('id')
            ->where([
                'id' => $model->{$property->key}
            ])
            ->column();

        return $this->render(
            $this->convertView($view),
            [
                'data' => $data,
                'model' => $model,
                'property' => $property,
                'form' => $form
            ]
        );
    }
}
