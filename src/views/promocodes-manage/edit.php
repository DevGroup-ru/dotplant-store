<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model DotPlant\Store\models\order\Promocode */

$this->title = Yii::t('dotplant.store', 'Update {modelClass}: ', [
    'modelClass' => 'Promocode',
]) . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('dotplant.store', 'Promocodes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('dotplant.store', 'Update');
?>
<div class="promocode-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
