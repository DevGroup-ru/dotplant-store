<?php

namespace DotPlant\Store\models\order;

use Yii;

/**
 * This is the model class for table "{{%dotplant_store_promocode}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $is_active
 * @property string $promocode_string
 * @property integer $is_unlimited
 * @property integer $available_count
 */
class Promocode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dotplant_store_promocode}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'promocode_string'], 'required'],
            [['is_active', 'is_unlimited', 'available_count'], 'integer'],
            [['name', 'promocode_string'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('dotplant.store', 'ID'),
            'name' => Yii::t('dotplant.store', 'Name'),
            'is_active' => Yii::t('dotplant.store', 'Is Active'),
            'promocode_string' => Yii::t('dotplant.store', 'Promocode String'),
            'is_unlimited' => Yii::t('dotplant.store', 'Is Unlimited'),
            'available_count' => Yii::t('dotplant.store', 'Available Count'),
        ];
    }

    public function checkPromocode($string)
    {
        return self::_generateSecureString($string) === $this->promocode_string;
    }

    public static function getByString($string)
    {
        $promocode = self::findOne(['promocode_string' => self::_generateSecureString($string)]);
        if (is_object($promocode)) {
            return $promocode;
        }
        throw new \Exception('No promocode found');
    }

    public static function generatePromocode($string, $name, Promocode $model = null)
    {
        if ($model === null) {
            $model = new self;
            $model->loadDefaultValues();
        }
        $model->promocode_string = self::_generateSecureString($string);
        $model->name = $name;
        if ($model->validate() && $model->save()) {
            return $model;
        }
        throw new \Exception(implode(',', $model->errors));
    }

    public static function deactivatePromocode($string)
    {
        $promocode = self::getByString($string);
        $promocode->is_active = 0;
        $promocode->save();
    }

    public static function usePromocode($string)
    {
        $promocode = self::getByString($string);
        if ($promocode->is_active) {
            if (boolval($promocode->is_unlimited) === false) {
                if ($promocode->available_count > 0) {
                    $promocode->available_count--;
                    $promocode->save();
                    return true;
                }
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @param $string
     *
     * @return string
     */
    private static function _generateSecureString($string)
    {
        return password_hash($string, PASSWORD_DEFAULT);
    }

}
