<?php

/**
 * @var int $languageId
 * @var string $password
 * @var \yii\web\View $this
 * @var int $userId
 */

/** @var \DevGroup\Multilingual\models\Language $language */
$language = call_user_func([Yii::$app->multilingual->modelsMap['Language'], 'findOne'], $languageId);
$domain = $language !== null ? $language->domain : 'Unknown domain';

?>
You has been successfully registered at <?= $domain ?>
