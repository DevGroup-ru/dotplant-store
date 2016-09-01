<?php

use yii\db\Migration;
use DotPlant\EntityStructure\models\Entity;
use DotPlant\EntityStructure\models\BaseStructure;

class m160901_113122_dotplant_goods_add_extended_price extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable(
            '{{%dotplant_store_extended_price}}',
            [
                'id' => $this->primaryKey(),
                'entity_id' => $this->integer()->notNull()->defaultValue(0),
                'entity_model_id' => $this->integer()->notNull(),
                'start_time' => $this->integer(),
                'end_time' => $this->integer(),
                'is_percent' => $this->boolean()->notNull()->defaultValue(true),
                'value' => $this->decimal(10, 2)->notNull()->defaultValue(0),
                'mode' => $this->integer()->notNull()->defaultValue(1)
            ],
            $tableOptions
        );
        $this->createIndex('idx-ext-price-e_id-em_id', '{{%dotplant_store_extended_price}}', ['entity_id', 'entity_model_id'], true);
        $this->createIndex('idx-ext-price-st_time', '{{%dotplant_store_extended_price}}', 'start_time');
        $this->createIndex('idx-ext-price-end_time', '{{%dotplant_store_extended_price}}', 'end_time');

        $this->addForeignKey('fk-ext-price-e_id-ent-id', '{{%dotplant_store_extended_price}}', 'entity_id', Entity::tableName(), 'id', 'CASCADE');
        $this->addForeignKey('fk-ext-price-em_id-struct-id', '{{%dotplant_store_extended_price}}', 'entity_model_id', BaseStructure::tableName(), 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk-ext-price-e_id-ent-id', '{{%dotplant_store_extended_price}}');
        $this->dropForeignKey('fk-ext-price-em_id-struct-id', '{{%dotplant_store_extended_price}}');

        $this->dropIndex('idx-ext-price-e_id-em_id', '{{%dotplant_store_extended_price}}');
        $this->dropIndex('idx-ext-price-st_time', '{{%dotplant_store_extended_price}}');
        $this->dropIndex('idx-ext-price-end_time', '{{%dotplant_store_extended_price}}');

        $this->dropTable('{{%dotplant_store_extended_price}}');
    }
}
