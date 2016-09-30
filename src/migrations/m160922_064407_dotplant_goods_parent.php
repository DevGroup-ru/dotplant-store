<?php

use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\models\goods\GoodsParent;
use yii\db\Migration;

class m160922_064407_dotplant_goods_parent extends Migration
{
    public function up()
    {
        $this->createTable(
            GoodsParent::tableName(),
            [
                'goods_id' => $this->integer()->notNull(),
                'goods_parent_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0)
            ]
        );
        $this->addPrimaryKey(
            'pk-goods_parent-goods_id-goods_parent_id',
            GoodsParent::tableName(),
            ['goods_id', 'goods_parent_id']
        );
        $this->addForeignKey(
            'fk-goods_id-goods_id',
            GoodsParent::tableName(),
            'goods_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-goods_id-goods_parent_id',
            GoodsParent::tableName(),
            'goods_parent_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk-goods_id-goods_parent_id', GoodsParent::tableName());
        $this->dropForeignKey('fk-goods_id-goods_id', GoodsParent::tableName());
        $this->dropTable(GoodsParent::tableName());
    }
}
