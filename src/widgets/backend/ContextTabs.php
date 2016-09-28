<?php

namespace DotPlant\Store\widgets\backend;

use DevGroup\Multilingual\models\Context;
use yii\base\Widget;

/**
 * Class ContextTabs
 * @package DotPlant\Store\widgets\backend
 */
class ContextTabs extends Widget
{
    /**
     * @var int the context id
     */
    public $contextId;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->contextId === null) {
            $this->contextId = \Yii::$app->request->get('contextId', \Yii::$app->multilingual->context_id);
        }
        $tabs = [];
        foreach (call_user_func([\Yii::$app->multilingual->modelsMap['Context'], 'find'])->all() as $context) {
            $tabs[] = [
                'active' => $context->id == $this->contextId,
                'label' => $context->name,
                'url' => ['index', 'contextId' => $context->id],
            ];
        }
        echo $this->render(
            'context-tabs',
            [
                'tabs' => $tabs,
            ]
        );
    }
}
