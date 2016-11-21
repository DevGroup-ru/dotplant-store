<?php

namespace DotPlant\Store\models\price;

use DotPlant\Store\components\calculator\BundleCalculator;
use DotPlant\Store\interfaces\PriceInterface;
use yii\helpers\ArrayHelper;

/**
 * Class BundlePrice
 * @package DotPlant\Store\models\price
 */
class BundlePrice extends Price
{
    private $_lastPriceKey;
    protected $_calculatorClass = BundleCalculator::class;

    /**
     * @inheritdoc
     */
    public function getPrice(
        $warehouseId,
        $priceType = PriceInterface::TYPE_RETAIL,
        $withDiscount = true,
        $convertIsoCode = false
    ) {
        $this->_lastPriceKey = implode(
            ':',
            [$warehouseId, $priceType, $this->getGoods()->id, $convertIsoCode, $warehouseId, $withDiscount]
        );
        if (!isset(self::$_price[$this->_lastPriceKey])) {
            $this->_warehouseId = $warehouseId;
            $this->_priceType = $priceType;
            $this->_withDiscount = $withDiscount;
            $this->_convert_iso_code = $convertIsoCode;
            $price = false;
            foreach ($this->getGoods()->children as $child) {
                if ($child->is_active == 0) {
                    continue;
                }
                if (
                    ($childPrice = $child->getPrice($warehouseId, $priceType, $withDiscount, $convertIsoCode)) === false
                ) {
                    self::$_price[$this->_lastPriceKey] = false;
                    return self::$_price[$this->_lastPriceKey];
                }
                if ($price === false) {
                    $price = $childPrice;
                } else {
                    $price['value'] += $childPrice['value'];
                    $price['valueWithoutDiscount'] += $childPrice['valueWithoutDiscount'];
                    $price['discountReasons'] = ArrayHelper::merge($price['discountReasons'], $childPrice['discountReasons']);
                }
            }
            self::$_price[$this->_lastPriceKey] = $price;
            $calculatorClass = $this->_calculatorClass;
            $price = $calculatorClass::calculate($this);
            if (empty($price) === false) {
                $isoCode = $this->getConvertIsoCode();
                if ($isoCode && $isoCode !== $price['isoCode']) {
                    $price = $this->convert($price, $isoCode);
                }
                $price = ArrayHelper::merge($price, ['warehouseId' => $warehouseId]);
            }
            self::$_price[$this->_lastPriceKey] = $price;
        }
        return self::$_price[$this->_lastPriceKey];
    }

    /**
     * @inheritdoc
     */
    public function getMinPrice($priceType = PriceInterface::TYPE_RETAIL, $withDiscount = true, $convertIsoCode = false)
    {
        $this->_lastPriceKey = implode(
            ':',
            ['MinPrice', $priceType, $this->getGoods()->id, $convertIsoCode, $withDiscount]
        );
        if (!isset(self::$_price[$this->_lastPriceKey])) {
            $price = false;
            foreach ($this->getGoods()->children as $child) {
                if ($child->is_active == 0) {
                    continue;
                }
                if (($childPrice = $child->getMinPrice($priceType, $withDiscount, $convertIsoCode)) === false) {
                    $price = false;
                    break;
                }
                if ($price === false) {
                    $price = $childPrice;
                } else {
                    $price['value'] += $childPrice['value'];
                    $price['valueWithoutDiscount'] += $childPrice['valueWithoutDiscount'];
                    $price['discountReasons'] = array_merge($price['discountReasons'], $childPrice['discountReasons']);
                    if ($price['warehouseId'] !== $childPrice['warehouseId']) {
                        $price['warehouseId'] = null;
                    }
                }
            }
            self::$_price[$this->_lastPriceKey] = $price;
        }
        return self::$_price[$this->_lastPriceKey];
    }

    public function getLastPrice()
    {
        return isset(self::$_price[$this->_lastPriceKey])
            ? self::$_price[$this->_lastPriceKey]
            : false;
    }
}
