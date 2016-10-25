<?php

use DevGroup\DataStructure\models\PropertyHandlers;
use DotPlant\Store\propertyHandler\RelatedProducts;
use yii\db\Migration;

class m161025_103010_dotplant_store_properties_related_products extends Migration
{
    public function up()
    {
        $lastSortOrder = PropertyHandlers::find()
            ->select('sort_order')
            ->orderBy(['sort_order' => SORT_DESC])
            ->limit(1)
            ->scalar();

        $this->insert(
            PropertyHandlers::tableName(),
            [
                'name' => 'Related Products',
                'class_name' => RelatedProducts::class,
                'sort_order' => ($lastSortOrder + 1),
                'packed_json_default_config' => '[]',
            ]
        );
    }

    public function down()
    {
        $this->delete(
            PropertyHandlers::tableName(),
            [
                'class_name' => RelatedProducts::class,
            ]
        );
    }
}
