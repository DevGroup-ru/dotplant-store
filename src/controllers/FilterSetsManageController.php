<?php

namespace DotPlant\Store\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\AdminUtils\Helper;
use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\StaticValue;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\filters\FilterSetsModel;
use DotPlant\Store\models\filters\FilterStaticValueModel;
use DotPlant\Store\models\filters\StructureFilterSets;
use DotPlant\Store\models\filters\StructureFilterValue;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FilterSetsManageController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['dotplant-store-filter-sets-view'],
                    ],
                    [
                        'actions' => ['add-set', 'delete-set', 'update-set', 'update-set-value'],
                        'allow' => true,
                        //@todo roles
                    ],
                    [
                        'allow' => false,
                        'roles' => ['*'],
                    ],
                ],
            ],
            'verb' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update-set' => ['post'],
                    'delete-set' => ['post'],
                    'update-set-value' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex($id = null)
    {
        if ($id !== null) {
            $this->guardEntityExist($id);
            return $this->render(
                'index',
                [
                    'parentId' => $id,
                    'selectorData' => $this->getSelectorDataFromSet($this->getAvailableSets($id), $id),
                    'filterSets' => $this->getAttachedFilterSets($id),
                ]
            );
        } else {
            return $this->render('index', ['parentId' => null]);
        }
    }

    public function actionAddSet($entityId, $propertyId, $propertyGroupId, $returnUrl)
    {
        $this->guardEntityExist($entityId);
        $this->guardPropertyExist($propertyId);
        $this->guardPropertyGroupExist($propertyGroupId);
        $this->guardSetNotExist($entityId, $propertyId, $propertyGroupId);
        $this->addSet($entityId, $propertyId, $propertyGroupId);
        return $this->redirect($returnUrl);
    }

    public function actionDeleteSet($indx)
    {
        list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
        $this->guardSetIsExist($entityId, $propertyId, $propertyGroupId);
        $set = $this->getFilterSet($entityId, $propertyId, $propertyGroupId);
        $set->delete();
    }

    public function actionUpdateSet()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $indx = Yii::$app->request->post('editableKey');
        list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
        $this->guardSetIsExist($entityId, $propertyId, $propertyGroupId);
        return $this->updateSet($entityId, $propertyId, $propertyGroupId);
    }

    public function actionUpdateSetValue()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $indx = Yii::$app->request->post('editableKey');
        $attribute = Yii::$app->request->post('editableAttribute');
        $value = $this->processValue(Yii::$app->request->post($attribute));
        return $this->updateSetValue($indx, $attribute, $value);

    }


    private function getAvailableSets($entityId)
    {
        /**
         * @var $propertyGroups PropertyGroup[]
         */
        $propertyGroups = PropertyGroup::find()->all();
        $result = [];
        $attached = $this->getAttachedFilterSets($entityId);
        foreach ($propertyGroups as $propertyGroup) {
            $result[$propertyGroup->id] = [
                'id' => $propertyGroup->id,
                'name' => $propertyGroup->name,
                'properties' => [],
            ];
            foreach ($propertyGroup->properties as $property) {
                $indx = "$entityId.{$propertyGroup->id}.{$property->id}";
                if (!array_key_exists($indx, $attached)) {
                    $result[$propertyGroup->id]['properties'][] = [
                        'id' => $property->id,
                        'name' => $property->name,
                    ];
                }
            }
        }
        return $result;
    }

    private function getAttachedFilterSets($entityId)
    {
        /**
         * @var $filterSetsFromDb FilterSetsModel[]
         */
        $filterSetsFromDb = FilterSetsModel::find()->where(['structure_id' => $entityId])->orderBy(
            ['sort_order' => SORT_ASC]
        )->with(['property', 'group'])->all();
        $sets = [];
        foreach ($filterSetsFromDb as $filterSetFromDb) {
            /**
             * @var $filterSetValuesFromDb FilterStaticValueModel[]
             */
            // @todo разделить код работающий с базой и с моделями

            $filterSetValuesFromDb = FilterStaticValueModel::find()->where(
                ['filter_set_id' => $filterSetFromDb->id]
            )->with('staticValue')->orderBy(['sort_order' => SORT_ASC])->all();
            $filterStaticValuesIds = ArrayHelper::getColumn($filterSetValuesFromDb, 'static_value_id');
            /**
             * @var $staticValuesNotInFilter StaticValue[]
             */
            $staticValuesNotInFilter = StaticValue::find()->where(['not in', 'id', $filterStaticValuesIds])->andWhere(
                ['property_id' => $filterSetFromDb->property->id]
            )->all();
            if (count($staticValuesNotInFilter) > 0) {
                foreach ($staticValuesNotInFilter as $staticValue) {
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
                    $filterSetValuesFromDb[] = $model;
                }
            }
            $values = [];
            foreach ($filterSetValuesFromDb as $filterSetValueFromDb) {
                $indx = implode('.', [$filterSetValueFromDb->filter_set_id, $filterSetValueFromDb->static_value_id]);
                $values[$indx] = (new StructureFilterValue(
                    $filterSetValueFromDb->staticValue->name,
                    $filterSetValueFromDb->staticValue->slug,
                    $filterSetValueFromDb->sort_order,
                    boolval($filterSetValueFromDb->display)
                ));
            }
            $indx = "$entityId.{$filterSetFromDb->group->id}.{$filterSetFromDb->property->id}";
            $sets[$indx] = (new StructureFilterSets(
                $filterSetFromDb->property->name,
                $filterSetFromDb->group->internal_name,
                $entityId,
                $filterSetFromDb->property_id,
                $filterSetFromDb->sort_order,
                boolval($filterSetFromDb->delegate_to_child),
                $filterSetFromDb->group_id,
                $values
            ));
        }
        return $sets;
    }

    private function getSelectorDataFromSet(array $groups, $entityId)
    {
        $result = [];
        foreach ($groups as $group) {
            $tmp = ['label' => $group['name'], 'items' => []];
            foreach ($group['properties'] as $property) {
                $tmp['items'][] = [
                    'label' => $property['name'],
                    'url' => Url::to(
                        [
                            '/store/filter-sets-manage/add-set',
                            'entityId' => $entityId,
                            'propertyId' => $property['id'],
                            'propertyGroupId' => $group['id'],
                            'returnUrl' => Helper::returnUrl(),
                        ]
                    ),
                ];
            }
            $result[] = $tmp;
        }
        return $result;
    }

    private function getEntity($entityId)
    {
        return BaseStructure::findOne($entityId);
    }

    private function guardEntityExist($entityId)
    {
        if ($entityId !== null) {
            $entity = $this->getEntity($entityId);
            if ($entity === null) {
                throw new NotFoundHttpException(\Yii::t('app', 'Entity not exist'));
            }
        }
    }

    private function guardSetIsExist($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = $this->getFilterSet($entityId, $propertyId, $propertyGroupId);
        if ($filterSet === null) {
            throw new \DomainException(\Yii::t('app', 'Filter set not exist'));
        }
    }

    private function guardSetNotExist($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = $this->getFilterSet($entityId, $propertyId, $propertyGroupId);
        if ($filterSet !== null) {
            throw new \DomainException(\Yii::t('app', 'Filter set already exist'));
        }
    }

    private function guardPropertyExist($propertyId)
    {
        $property = Property::findOne($propertyId);
        if ($property === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Property not exist'));
        }
    }

    private function guardPropertyGroupExist($propertyGroupId)
    {
        $property = PropertyGroup::findOne($propertyGroupId);
        if ($property === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Property group not exist'));
        }
    }

    private function getNextSortOrderForSet()
    {
        return 0;
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @param $propertyGroupId
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    private function getFilterSet($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = FilterSetsModel::find()->where(
            ['structure_id' => $entityId, 'property_id' => $propertyId, 'group_id' => $propertyGroupId]
        )->one();
        return $filterSet;
    }

    private function processValue($value)
    {
        if (empty($value)) {
            return 0;
        } elseif ($value === 'on') {
            return 1;
        }
        return $value;
    }

    /**
     * @param $entityId
     * @param $propertyId
     * @param $propertyGroupId
     */
    private function addSet($entityId, $propertyId, $propertyGroupId)
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
     *
     * @return mixed
     */
    private function updateSet($entityId, $propertyId, $propertyGroupId): mixed
    {
        $set = $this->getFilterSet($entityId, $propertyId, $propertyGroupId);
        $attribute = Yii::$app->request->post('editableAttribute');
        $value = $this->processValue(Yii::$app->request->post($attribute));
        $set->setAttribute($attribute, $value);
        $set->save();
        // @todo think about frontend edit messages
        return $set->getAttribute($attribute);
    }

    private function updateSetValue($indx, $attribute, $value)
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

    private function guardSetStaticValueExist($filterSetId, $staticValueId)
    {
        $value = $this->getFilterStaticValue($filterSetId, $staticValueId);
        if ($value === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Filter static value not exist'));
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

    private function getStaticValue($staticValueId)
    {
        return StaticValue::findOne($staticValueId);
    }

    private function guardStaticValueExist($staticValueId)
    {
        $staticValue = $this->getStaticValue($staticValueId);
        if ($staticValue === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Static value not exist'));
        }
    }
}
