<?php

namespace DotPlant\Store\actions\goods;

use DevGroup\AdminUtils\actions\BaseAdminAction;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsTranslation;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\db\Query;
use Yii;

/**
 * Class GoodsAutocompleteAction
 *
 * @package DotPlant\Store\actions\goods
 */
class GoodsAutocompleteAction extends BaseAdminAction
{
    /**
     * @var array fields to search against
     */
    public $searchFields;

    /**
     * @var array default search fields
     */
    private $defaultFields = ['title', 'name', 'sku', 'inner_sku'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException(Yii::t('dotplant.store', 'Page not found'));
        }
        if (false === Yii::$app->user->can('backend-view')) {
            throw new ForbiddenHttpException(Yii::t('dotplant.store', 'You are not allowed to perform this action.'));
        }
        if (false === empty($this->searchFields)) {
            $translationsColumns = GoodsTranslation::getTableSchema()->columnNames;
            $goodsColumns = Goods::getTableSchema()->columnNames;
            $columns = array_merge($translationsColumns, $goodsColumns);
            $notFound = array_diff($this->searchFields, $columns);
            if (false === empty($notFound)) {
                throw new InvalidConfigException(
                    Yii::t(
                        'dotplant.store',
                        'The following columns \'{columns}\' are not found in database table!',
                        ['columns' => implode(', ', $notFound)]
                    )
                );
            }
        } else {
            $this->searchFields = $this->defaultFields;
        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run($q = null, $excludedIds = null, $id = null, $allowedTypes = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (null !== $q) {
            $query = new Query;
            if ($excludedIds !== null) {
                $query->where(['not in', 'id', (array) $excludedIds]);
            }
            if ($allowedTypes !== null) {
                $query->andWhere(['type' => (array) $allowedTypes]);
            }
            $query
                ->select(new Expression("id, CONCAT(name, ' (', sku, ')') AS text"))
                ->from(Goods::tableName())
                ->innerJoin(GoodsTranslation::tableName(), 'id = model_id')
                ->andWhere(
                    ['language_id' => Yii::$app->multilingual->language_id]
                )
                ->andWhere($this->prepareCondition($q))
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Goods::find($id)->name];
        }
        return $out;
    }

    /**
     * Prepares query condition
     *
     * @param $q
     *
     * @return array|int
     */
    private function prepareCondition($q)
    {
        $condition = [];
        foreach ($this->searchFields as $field) {
            $condition[] = ['like', $field, $q];
        }
        if (count($this->searchFields) > 1) {
            array_unshift($condition, 'or');
        }
        return $condition;
    }
}
