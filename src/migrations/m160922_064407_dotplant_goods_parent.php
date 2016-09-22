<?php

use yii\db\Migration;

class m160922_064407_dotplant_goods_parent extends Migration
{
    public function up()
    {
        $this->dropIndex('idx-goods-parent_id', '{{%dotplant_store_goods}}');
        $this->dropColumn(
            '{{%dotplant_store_goods}}',
            'parent_id'
        );

        $this->createTable(
            '{{%dotplant_store_goods_parent}}',
            [
                'goods_id' => $this->integer()->notNull(),
                'goods_parent_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0)
            ]
        );

        $this->addPrimaryKey(
            'pk-goods_parent-goods_id-goods_parent_id',
            '{{%dotplant_store_goods_parent}}',
            ['goods_id', 'goods_parent_id']
        );

        $this->addForeignKey(
            'fk-goods_id-goods_id',
            '{{%dotplant_store_goods_parent}}',
            'goods_id',
            '{{%dotplant_store_goods}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-goods_id-goods_parent_id',
            '{{%dotplant_store_goods_parent}}',
            'goods_parent_id',
            '{{%dotplant_store_goods}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-goods_id-goods_parent_id', '{{%dotplant_store_goods_parent}}');
        $this->dropForeignKey('fk-goods_id-goods_id', '{{%dotplant_store_goods_parent}}');

        $this->dropTable('{{%dotplant_store_goods_parent}}');

        $this->addColumn(
            '{{%dotplant_store_goods}}',
            'parent_id',
            $this->integer()
        );

        $this->createIndex('idx-goods-parent_id', '{{%dotplant_store_goods}}', 'parent_id');
    }
}
