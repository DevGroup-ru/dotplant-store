<?php

use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\goods\Goods;
use DotPlant\Store\repository\Yii2DbGoodsMainCategory;
use yii\db\Migration;
use yii\db\Query;

class m161205_091237_main_structure extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB' : null;

        $this->createTable(
            Yii2DbGoodsMainCategory::TABLE_NAME,
            [
                'goods_id' => $this->integer()->notNull(),
                'main_structure_id' => $this->integer()->notNull(),
                'context_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addPrimaryKey(
            'idx-goods_main_structure-goods_id-main_structure_id-context_id',
            Yii2DbGoodsMainCategory::TABLE_NAME,
            ['goods_id', 'main_structure_id', 'context_id']
        );

        $this->addForeignKey(
            'fk-goods_main_structure-goods-goods_id-id',
            Yii2DbGoodsMainCategory::TABLE_NAME,
            'goods_id',
            Goods::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-goods_main_structure-structure-main_structure_id',
            Yii2DbGoodsMainCategory::TABLE_NAME,
            'main_structure_id',
            BaseStructure::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $rows = (new Query())->select(
            [Goods::tableName() . '.id', 'main_structure_id', BaseStructure::tableName() . '.context_id']
        )->from(
            Goods::tableName()
        )->leftJoin(BaseStructure::tableName(), 'main_structure_id=' . BaseStructure::tableName() . '.id')->all();

        $this->batchInsert(
            Yii2DbGoodsMainCategory::TABLE_NAME,
            ['goods_id', 'main_structure_id', 'context_id'],
            $rows
        );
        $this->dropColumn(Goods::tableName(), 'main_category_id');
    }

    public function down()
    {
        $this->addColumn(Goods::tableName(), 'main_category_id', $this->integer());
        $this->dropTable(Yii2DbGoodsMainCategory::TABLE_NAME);
    }
}
