<?php

/**
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var \yii\web\View $this
 */

use yii\bootstrap\Tabs;

?>
<?=
Tabs::widget(
    [
        'items' => [
            [
                'label' => Yii::t('dotplant.store', 'Order'),
                'content' => $this->render('@DotPlant/Store/views/_order', ['form' => $form, 'model' => $model]),
                'active' => true,
            ]
        ],
    ]
);
