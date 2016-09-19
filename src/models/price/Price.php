<?php

namespace DotPlant\Store\models\price;

use DotPlant\Currencies\helpers\CurrencyHelper;
use DotPlant\Store\exceptions\PriceException;
use DotPlant\Store\interfaces\PriceInterface;
use DotPlant\Store\models\goods\Goods;
use Yii;

abstract class Price implements PriceInterface
{
    protected $_calculatorClass = null;

    protected $_goods = null;

    protected $_warehouseId = null;

    protected $_priceType = null;

    protected $_extendedPrice = null;

    protected $_convert_iso_code = false;

    /**
     * @var bool
     */
    protected $_withDiscount = true;

    private static $_priceMap = [];


    private $_price = [];

    /**
     * @inheritdoc
     */
    public static function create(Goods $goods)
    {
        /** @var Price | string $priceClass */
        $priceClass = get_called_class();
        if (false === is_subclass_of($priceClass, Price::class)) {
            throw new PriceException(
                Yii::t('dotplant.store', 'Attempting to get unknown price type')
            );
        }
        if (false === isset(self::$_priceMap[$priceClass])) {
            if (false === $goods->getIsNewRecord()) {
                $price = new $priceClass;
            } else {
                $price = new DummyPrice();
            }
            self::$_priceMap[$priceClass] = $price;
        } else {
            $price = self::$_priceMap[$priceClass];
        }

        /* @var $price Price */
        $price->_goods = $goods;
        return $price;
    }



    public static function convert($price, $to)
    {

        $price['original_iso_code'] = $price['iso_code'];
        $price['original_value'] = $price['value'];
        $price['iso_code'] = $to;
        $price['value'] = CurrencyHelper::convertCurrencies(
            $price['value'],
            CurrencyHelper::findCurrencyByIso($price['original_iso_code']),
            CurrencyHelper::findCurrencyByIso($to)
        );
        return $price;
    }

    public function format($price, $format)
    {
        // TODO: Implement format() method.
    }


    /**
     * @param $warehouseId
     * @param string $priceType
     * @param bool|true $withDiscount
     * @param bool|false $convertIsoCode
     * @return mixed
     */
    public function getPrice($warehouseId, $priceType = PriceInterface::TYPE_RETAIL, $withDiscount = true, $convertIsoCode = false)
    {

        $priceKey = implode(':', [
            $warehouseId,
            $priceType,
            $this->getGoods()->id,
            $convertIsoCode,
            $warehouseId,
            $withDiscount
        ]);

        if (empty($this->_price[$priceKey]) === true) {
            $price = [];
            $this->_warehouseId = $warehouseId;
            $this->_priceType = $priceType;
            $this->_withDiscount = $withDiscount;
            $this->_convert_iso_code = $convertIsoCode;

            $calculatorClass = $this->_calculatorClass;
            $price = $calculatorClass::calculate($this);

            if (empty($price) === false && $this->getConvertIsoCode()) {
                if ($this->getConvertIsoCode() !== $price['iso_code']) {
                    $price = $this->convert($price, $this->getConvertIsoCode());
                }
            }
            $this->_price[$priceKey] = $price;
        }

        return $this->_price[$priceKey];
    }

    /**
     * @return bool
     */
    public function isWithDiscount()
    {
        return $this->_withDiscount;
    }

    /**
     * @return boolean
     */
    public function getConvertIsoCode()
    {
        return $this->_convert_iso_code;
    }


    /**
     * @return int
     */
    public function getWarehouseId()
    {
        return $this->_warehouseId;
    }

    /**
     * @return string
     */
    public function getPriceType()
    {
        return $this->_priceType;
    }

    /**
     * @param $price
     */
    public function setPrice($price)
    {
        // TODO: Implement setPrice() method.
    }

    /**
     * @return Goods
     */
    public function getGoods()
    {
        return $this->_goods;
    }
}
