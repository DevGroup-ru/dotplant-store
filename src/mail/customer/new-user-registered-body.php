<?php

/**
 * @var int $languageId
 * @var string $password
 * @var \yii\web\View $this
 * @var int $userId
 */

use DevGroup\Users\helpers\ModelMapHelper;

$user = call_user_func([ModelMapHelper::User()['class'], 'findOne'], $userId);
$language = call_user_func([Yii::$app->multilingual->modelsMap['Language'], 'findOne'], $languageId);
$domain = $language !== null ? $language->domain : 'Unknown domain';

?>
<p>Hello, <?= $user->username ?>. You has been registered at <?= $domain ?>.</p>

<p>Your login data:</p>

<p>
    Login: <?= $user->username ?><br />
    Password: <?= $password ?>
</p>

<p>Please change the password at the first login.</p>
