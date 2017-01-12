<?php

namespace DotPlant\Store\models\filters;

use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\StaticValue;
use DotPlant\EntityStructure\models\BaseStructure;
use yii\web\NotFoundHttpException;

class FiltersRepository
{
    /**
     * @param $entityId
     *
     * @return BaseStructure
     */
    public function getEntity($entityId)
    {
        return BaseStructure::findOne($entityId);
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @param $propertyGroupId
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getFilterSet($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = FilterSetsModel::find()->where(
            ['structure_id' => $entityId, 'property_id' => $propertyId, 'group_id' => $propertyGroupId]
        )->one();
        return $filterSet;
    }


    /**
     * @return array|PropertyGroup[]
     */
    public function getAllPropertyGroups()
    {
        $propertyGroups = PropertyGroup::find()->all();
        return $propertyGroups;
    }

    /**
     * @param $entityId
     *
     * @return array|FilterSetsModel[]
     */
    public function getFilterSetByEntityId($entityId)
    {
        $filterSetsFromDb = FilterSetsModel::find()->where(['structure_id' => $entityId])->orderBy(
            ['sort_order' => SORT_ASC]
        )->with(['property', 'group'])->all();
        return $filterSetsFromDb;
    }

    /**
     * @param $entityId
     *
     * @return array|FilterSetsModel[]
     */
    public function getDelegatedByParentFilterSets($entityId)
    {
        $entity = $this->getEntity($entityId);
        $parentIds = $entity->getParentsIds();
        $filterSetsFromDb = FilterSetsModel::find()->where(
            ['structure_id' => $parentIds, 'delegate_to_child' => 1]
        )->orderBy(
            ['sort_order' => SORT_ASC]
        )->with(['property', 'group'])->all();
        return $filterSetsFromDb;
    }

    /**
     * @param $filterSetFromDb
     *
     * @return array|FilterStaticValueModel[]
     */
    public function getFilterStaticValuesByFilterSet($filterSetFromDb)
    {
        $filterSetValuesFromDb = FilterStaticValueModel::find()->where(
            ['filter_set_id' => $filterSetFromDb->id]
        )->with('staticValue')->orderBy(['sort_order' => SORT_ASC])->all();
        return $filterSetValuesFromDb;
    }

    /**
     * @param $filterStaticValuesIds
     * @param $filterSetFromDb
     *
     * @return array|StaticValue[]
     */
    public function getStaticValuesNotInFilter($filterStaticValuesIds, $filterSetFromDb)
    {
        $staticValuesNotInFilter = StaticValue::find()->where(['not in', 'id', $filterStaticValuesIds])->andWhere(
            ['property_id' => $filterSetFromDb->property->id]
        )->all();
        return $staticValuesNotInFilter;
    }

    /**
     * @param $propertyId
     *
     * @return Property
     */
    public function getProperty($propertyId)
    {
        $property = Property::findOne($propertyId);
        return $property;
    }

    /**
     * @param $propertyGroupId
     *
     * @return PropertyGroup
     */
    public function getPropertyGroup($propertyGroupId)
    {
        $propertyGroup = PropertyGroup::findOne($propertyGroupId);
        return $propertyGroup;
    }

    /**
     * @param $staticValue
     * @param $filterSetFromDb
     *
     * @return FilterStaticValueModel
     */
    public function createFilterStaticValue($staticValue, $filterSetFromDb)
    {
        $model = new FilterStaticValueModel();
        $model->setAttributes(
            [
                'static_value_id' => $staticValue->id,
                'sort_order' => $staticValue->sort_order,
                'display' => 0,
                'filter_set_id' => $filterSetFromDb->id,
            ]
        );
        $model->save();
        return $model;
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @param $propertyGroupId
     */
    public function createSet($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = new FilterSetsModel();
        $filterSet->loadDefaultValues();
        $filterSet->setAttributes(
            [
                'structure_id' => $entityId,
                'property_id' => $propertyId,
                'group_id' => $propertyGroupId,
                'sort_order' => $this->getNextSortOrderForSet(),
            ]
        );
        $filterSet->save();
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @param $propertyGroupId
     * @param $attribute
     * @param $value
     *
     * @return mixed
     */
    public function updateSet($entityId, $propertyId, $propertyGroupId, $attribute, $value)
    {
        $set = $this->getFilterSet($entityId, $propertyId, $propertyGroupId);
        $set->setAttribute($attribute, $value);
        $set->save();
        // @todo think about frontend edit messages
        return $set->getAttribute($attribute);
    }

    /**
     * @param $indx
     * @param $attribute
     * @param $value
     *
     * @return mixed
     */
    public function updateSetValue($indx, $attribute, $value)
    {
        list($filterSetId, $staticValueId) = explode('.', $indx);
        $this->guardSetStaticValueExist($filterSetId, $staticValueId);
        $this->guardStaticValueExist($staticValueId);
        $staticValueAttributesList = ['value' => 'name', 'slug' => 'slug'];
        if (array_key_exists($attribute, $staticValueAttributesList)) {
            $staticValue = $this->getStaticValue($staticValueId);
            $staticValue->getTranslation()->setAttribute($staticValueAttributesList[$attribute], $value);
            $staticValue->getTranslation()->save();
            return $staticValue->getTranslation()->getAttribute($staticValueAttributesList[$attribute]);
        }
        $filterStaticValue = $this->getFilterStaticValue($filterSetId, $staticValueId);
        $filterStaticValue->setAttribute($attribute, $value);
        $filterStaticValue->save();
        return $filterStaticValue->getAttribute($attribute);
    }

    /**
     * @return int
     */
    private function getNextSortOrderForSet()
    {
        return 0;
    }

    /**
     * @param $filterSetId
     * @param $staticValueId
     *
     * @throws NotFoundHttpException
     */
    private function guardSetStaticValueExist($filterSetId, $staticValueId)
    {
        $value = $this->getFilterStaticValue($filterSetId, $staticValueId);
        if ($value === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Filter static value not exist'));
        }
    }

    /**
     * @param $staticValueId
     *
     * @throws NotFoundHttpException
     */
    private function guardStaticValueExist($staticValueId)
    {
        $staticValue = $this->getStaticValue($staticValueId);
        if ($staticValue === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Static value not exist'));
        }
    }

    /**
     * @param $filterSetId
     * @param $staticValueId
     *
     * @return FilterStaticValueModel
     */
    private function getFilterStaticValue($filterSetId, $staticValueId)
    {
        $value = FilterStaticValueModel::findOne(
            ['filter_set_id' => $filterSetId, 'static_value_id' => $staticValueId]
        );
        return $value;
    }

    /**
     * @param $staticValueId
     *
     * @return StaticValue
     */
    private function getStaticValue($staticValueId)
    {
        return StaticValue::findOne($staticValueId);
    }
}
