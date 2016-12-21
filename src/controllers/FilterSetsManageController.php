<?php

namespace DotPlant\Store\controllers;

use DevGroup\AdminUtils\controllers\BaseController;
use DevGroup\AdminUtils\Helper;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\StaticValue;
use DotPlant\Store\models\filters\FilterSetsModel;
use DotPlant\Store\models\filters\FiltersRepository;
use DotPlant\Store\models\filters\FilterStaticValueModel;
use DotPlant\Store\models\filters\StructureFilterSets;
use DotPlant\Store\models\filters\StructureFilterValue;
use DotPlant\Store\Module;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FilterSetsManageController extends BaseController
{
    /**
     * @var FiltersRepository
     */
    private $filtersRepository;

    public function __construct($id, Module $module, FiltersRepository $filtersRepository, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->filtersRepository = $filtersRepository;
    }

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
        $this->filtersRepository->createSet($entityId, $propertyId, $propertyGroupId);
        return $this->redirect($returnUrl);
    }

    public function actionDeleteSet($indx)
    {
        list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
        $this->guardSetIsExist($entityId, $propertyId, $propertyGroupId);
        $set = $this->filtersRepository->getFilterSet($entityId, $propertyId, $propertyGroupId);
        $set->delete();
    }

    public function actionUpdateSet()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $indx = Yii::$app->request->post('editableKey');
        list($entityId, $propertyGroupId, $propertyId) = explode('.', $indx);
        $this->guardSetIsExist($entityId, $propertyId, $propertyGroupId);
        $attribute = Yii::$app->request->post('editableAttribute');
        $value = $this->processValue(Yii::$app->request->post($attribute));
        return $this->filtersRepository->updateSet($entityId, $propertyId, $propertyGroupId, $attribute, $value);
    }

    public function actionUpdateSetValue()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $indx = Yii::$app->request->post('editableKey');
        $attribute = Yii::$app->request->post('editableAttribute');
        $value = $this->processValue(Yii::$app->request->post($attribute));
        return $this->filtersRepository->updateSetValue($indx, $attribute, $value);

    }


    private function getAvailableSets($entityId)
    {
        /**
         * @var $propertyGroups PropertyGroup[]
         */
        $propertyGroups = $this->filtersRepository->getAllPropertyGroups();
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
        $filterSetsFromDb = $this->filtersRepository->getFilterSetByEntityId($entityId);
        $sets = [];
        foreach ($filterSetsFromDb as $filterSetFromDb) {
            /**
             * @var $filterSetValuesFromDb FilterStaticValueModel[]
             */
            // @todo разделить код работающий с базой и с моделями

            $filterSetValuesFromDb = $this->filtersRepository->getFilterStaticValuesByFilterSet($filterSetFromDb);
            $filterStaticValuesIds = ArrayHelper::getColumn($filterSetValuesFromDb, 'static_value_id');
            /**
             * @var $staticValuesNotInFilter StaticValue[]
             */
            $staticValuesNotInFilter = $this->filtersRepository->getStaticValuesNotInFilter(
                $filterStaticValuesIds,
                $filterSetFromDb
            );
            if (count($staticValuesNotInFilter) > 0) {
                foreach ($staticValuesNotInFilter as $staticValue) {
                    $model = $this->filtersRepository->createFilterStaticValue($staticValue, $filterSetFromDb);
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

    private function guardEntityExist($entityId)
    {
        if ($entityId !== null) {
            $entity = $this->filtersRepository->getEntity($entityId);
            if ($entity === null) {
                throw new NotFoundHttpException(\Yii::t('app', 'Entity not exist'));
            }
        }
    }

    private function guardSetIsExist($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = $this->filtersRepository->getFilterSet($entityId, $propertyId, $propertyGroupId);
        if ($filterSet === null) {
            throw new \DomainException(\Yii::t('app', 'Filter set not exist'));
        }
    }

    private function guardSetNotExist($entityId, $propertyId, $propertyGroupId)
    {
        $filterSet = $this->filtersRepository->getFilterSet($entityId, $propertyId, $propertyGroupId);
        if ($filterSet !== null) {
            throw new \DomainException(\Yii::t('app', 'Filter set already exist'));
        }
    }

    private function guardPropertyExist($propertyId)
    {
        $property = $this->filtersRepository->getProperty($propertyId);
        if ($property === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Property not exist'));
        }
    }

    private function guardPropertyGroupExist($propertyGroupId)
    {
        $property = $this->filtersRepository->getPropertyGroup($propertyGroupId);
        if ($property === null) {
            throw new NotFoundHttpException(\Yii::t('app', 'Property group not exist'));
        }
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
}
