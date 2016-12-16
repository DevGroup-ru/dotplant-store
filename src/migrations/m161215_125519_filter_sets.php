<?php

use app\helpers\PermissionsHelper;
use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\StaticValue;
use DotPlant\EntityStructure\models\BaseStructure;
use DotPlant\Store\models\filters\FilterSetsModel;
use DotPlant\Store\models\filters\FilterStaticValueModel;
use yii\db\Migration;

class m161215_125519_filter_sets extends Migration
{
    private static $rules = [
        'FilterSetsAdministrator' => [
            'descr' => 'You can administrate filter sets',
            'permits' => [
                'dotplant-store-filter-sets-view' => 'You can see filter sets',
                'dotplant-store-filter-sets-create' => 'You can create a new filter sets',
                'dotplant-store-filter-sets-edit' => 'You can edit an filter sets',
                'dotplant-store-filter-sets-delete' => 'You can delete an filter sets',
            ],
        ],
        'FilterSetsManager' => [
            'descr' => 'You can filter sets',
            'permits' => [
                'dotplant-store-filter-sets-view' => 'You can see filter sets',
                'dotplant-store-filter-sets-create' => 'You can create a new filter sets',
                'dotplant-store-filter-sets-edit' => 'You can edit an filter sets',
            ],
        ],
    ];

    public function up()
    {
        PermissionsHelper::createPermissions(static::$rules);
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable(
            FilterSetsModel::tableName(),
            [
                'id' => $this->primaryKey(),
                'structure_id' => $this->integer()->notNull(),
                'property_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull(),
                'delegate_to_child' => $this->integer()->notNull(),
                'group_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk-dotplant_store_filter_sets-structure_id-dotplant_structure-id',
            FilterSetsModel::tableName(),
            'structure_id',
            BaseStructure::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_filter_sets-property_id-property-id',
            FilterSetsModel::tableName(),
            'property_id',
            Property::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-dotplant_store_filter_sets-group_id-property_group-id',
            FilterSetsModel::tableName(),
            'group_id',
            PropertyGroup::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->createTable(
            FilterStaticValueModel::tableName(),
            [
                'id' => $this->primaryKey(),
                'static_value_id' => $this->integer()->notNull(),
                'sort_order' => $this->integer()->notNull(),
                'display' => $this->integer()->notNull(),
                'filter_set_id' => $this->integer()->notNull(),
            ],
            $tableOptions
        );

        $this->addForeignKey(
            'fk-filter_static_value-static_value_id-static_value-id',
            FilterStaticValueModel::tableName(),
            'static_value_id',
            StaticValue::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk-filter_static_value-filter_set_id-filter_sets-id',
            FilterStaticValueModel::tableName(),
            'filter_set_id',
            FilterSetsModel::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable(FilterStaticValueModel::tableName());
        $this->dropTable(FilterSetsModel::tableName());
        PermissionsHelper::removePermissions(static::$rules);
    }

}
