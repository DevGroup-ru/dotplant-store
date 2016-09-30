<?php

use DotPlant\Emails\models\Template;
use yii\db\Migration;

class m160930_120108_dotplant_store_add_email_templates extends Migration
{
    private $_templates = [
        [
            'name' => 'New order email template (Manager)',
            'subject_view_file' => '@DotPlant/Store/mail/administrator/new-manager-order-subject.php',
            'body_view_file' => '@DotPlant/Store/mail/administrator/new-manager-order-body.php',
        ],
        [
            'name' => 'New order email template (Customer)',
            'subject_view_file' => '@DotPlant/Store/mail/customer/new-order-created-subject.php',
            'body_view_file' => '@DotPlant/Store/mail/customer/new-order-created-body.php',
        ],
        [
            'name' => 'Order status changing email template (Customer)',
            'subject_view_file' => '@DotPlant/Store/mail/customer/order-status-changed-subject.php',
            'body_view_file' => '@DotPlant/Store/mail/customer/order-status-changed-body.php',
        ],
        [
            'name' => 'New user email template (Customer)',
            'subject_view_file' => '@DotPlant/Store/mail/customer/new-user-registered-subject.php',
            'body_view_file' => '@DotPlant/Store/mail/customer/new-user-registered-body.php',
        ],
    ];

    public function up()
    {
        foreach ($this->_templates as $template) {
            $this->insert(Template::tableName(), $template);
        }
    }

    public function down()
    {
        foreach ($this->_templates as $template) {
            $this->delete(
                Template::tableName(),
                [
                    'subject_view_file' => $template['subject_view_file'],
                    'body_view_file' => $template['body_view_file'],
                ]
            );
        }
    }
}
