<?php

/**
 * @var array $tabs
 * @var \yii\web\View $this
 */

use yii\bootstrap\Tabs;

?>
<?=
Tabs::widget(
    [
        'items' => $tabs,
    ]
);
