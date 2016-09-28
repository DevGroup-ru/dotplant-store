<?php

use DevGroup\AdminUtils\Helper;
use DotPlant\Store\models\extendedPrice\ExtendedPrice;
use DotPlant\Store\models\extendedPrice\ExtendedPriceRule;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/***
 * @var View $this
 * @var ExtendedPrice $model
 * @var ExtendedPriceRule[] $extendedPriceRules
 * @var ExtendedPriceRule $newExtendedRule
 */
$this->title = Yii::t('app', 'Edit Extended price');

$this->params['breadcrumbs'][] = [
    'url' => ['/store/extended-price-manage/index'],
    'label' => Yii::t('app', 'Extended prices'),
];
$this->params['breadcrumbs'][] = $this->title;
?>


<section class="content">
    <div class="row">
        <div class="col-md-6">
            <?= $this->render(
                '_form',
                ['model' => $model, 'formAction' => Yii::$app->request->url, 'additionalFields' => []]
            ) ?>
        </div>
        <div class="col-md-6">
            <?php if (empty($handlers) === false) : ?>
                <div class="box box-solid">
                    <div class="box-header clearfix">
                        <h3 class="box-title pull-left">
                        </h3>
                    </div>
                    <div class="grid-view">
                        <div class="box-body">
                            <?php
                            $form = ActiveForm::begin();
                            echo $form->field($newExtendedRule, 'extended_price_handler_id')->dropDownList($handlers);
                            echo $form->field($newExtendedRule, 'priority');
                            echo $form->field(
                                $newExtendedRule,
                                'operand'
                            )->dropDownList(ExtendedPriceRule::getOperandList());
                            echo Html::submitButton(Yii::t('dotplant.store', 'Add'));
                            $form->end();

                            ?>
                        </div>

                    </div>
                </div>
            <?php endif; ?>
            <?php if (empty($extendedPriceRules) === false) : ?>
                <div class="box box-solid">
                    <div class="grid-view">
                        <div class="box-body">
                            <?php $form = ActiveForm::begin(); ?>
                            <?php foreach ($extendedPriceRules as $key => $rule) : ?>
                                <div class="row">
                                    <h3 class="col-md-12"><?= $rule->extendedPriceHandler->name; ?></h3>
                                    <div class="col-md-2">
                                        <?= $form->field($rule, "[$key]priority"); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                                        $handlerClass = $rule->extendedPriceHandler->handler_class;
                                        echo $handlerClass::renderForm($form, $rule);
                                        ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field(
                                            $rule,
                                            "[$key]operand"
                                        )->dropDownList(ExtendedPriceRule::getOperandList()); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?= Html::a(
                                            'Delete',
                                            [
                                                '/store/extended-price-manage/delete-rule',
                                                'id' => $rule->id,
                                                'returnUrl' => Helper::returnUrl(),
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="clearfix"></div>
                            <?= Html::submitButton('Save'); ?>
                            <?php $form->end(); ?>

                        </div>

                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>