<?php

use DevGroup\DataStructure\helpers\PropertiesHelper;
use DevGroup\DataStructure\helpers\PropertyHandlerHelper;
use DevGroup\DataStructure\models\Property;
use DevGroup\DataStructure\models\PropertyGroup;
use DevGroup\DataStructure\models\PropertyPropertyGroup;
use DevGroup\DataStructure\models\PropertyStorage;
use DevGroup\MediaStorage\helpers\MediaTableGenerator;
use DevGroup\MediaStorage\properties\MediaHandler;
use DevGroup\MediaStorage\properties\MediaStorage;
use DotPlant\Store\models\goods\Goods;
use yii\db\ActiveQuery;
use yii\db\Migration;

class m161005_122848_dotplant_store_default_property_image extends Migration
{
    public function up()
    {
        (new MediaTableGenerator())->generate(Goods::class);
        $propertyGroup = new PropertyGroup(Goods::class);
        $propertyGroup->internal_name = 'Goods images';
        $propertyGroup->is_auto_added = 1;
        foreach (Yii::$app->multilingual->getAllLanguages() as $id => $name) {
            $propertyGroup->translate($id)->name = 'Goods images';
        }
        $propertyGroup->save();


        $property = new Property();
        $property->key = 'goods_images';
        foreach (Yii::$app->multilingual->getAllLanguages() as $id => $name) {
            $property->translate($id)->name = 'images';
        }
        $property->data_type = Property::DATA_TYPE_STRING;
        $property->allow_multiple_values = 1;
        $property->storage_id = PropertyStorage::find()->where(['class_name' => MediaStorage::class])->scalar();
        $property->property_handler_id = PropertyHandlerHelper::getInstance()->handlerIdByClassName(MediaHandler::class);
        $saved = $property->save();

        $this->insert(
            PropertyPropertyGroup::tableName(),
            [
                'property_group_id' => $propertyGroup->id,
                'property_id' => $property->id
            ]
        );
    }

    public function down()
    {
        /** @var PropertyGroup $propertyGroup * */
        $propertyGroup = (new ActiveQuery(PropertyGroup::class))->where([
            'applicable_property_model_id' => PropertiesHelper::applicablePropertyModelId(Goods::class),
            'internal_name' => 'Goods images',
            'is_auto_added' => 1
        ])->one();

        $propertyGroup->hardDelete();

        /** @var Property $property * */
        $property = (new ActiveQuery(Property::class))->where([
            'key' => 'goods_images',
        ])->one();

        $property->hardDelete();


        $this->dropTable((new MediaTableGenerator())->getMediaTableName(Goods::class));
    }

}
