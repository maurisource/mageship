<?php
namespace Maurisource\MageShip\Model;

use Magento\Framework\Model\AbstractModel;
use Maurisource\MageShip\Api\Data\RatesInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * @method \Maurisource\MageShip\Model\ResourceModel\Rates getResource()
 * @method \Maurisource\MageShip\Model\ResourceModel\Rates\Collection getCollection()
 */
class Rates extends AbstractModel implements RatesInterface, IdentityInterface
{
    const CACHE_TAG = 'maurisource_mageship_rates';
    protected $_cacheTag = 'maurisource_mageship_rates';
    protected $_eventPrefix = 'maurisource_mageship_rates';

    protected function _construct()
    {
        $this->_init('Maurisource\MageShip\Model\ResourceModel\Rates');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getCarrierCode()
    {
        return $this->getData('carrier_code');
    }

    public function setCarrierCode($value)
    {
        return $this->setData('carrier_code', $value);
    }

    public function getCarrierTitle()
    {
        return $this->getData('carrier_title');
    }

    public function setCarrierTitle($value)
    {
        return $this->setData('carrier_title', $value);
    }

    public function getMethodName()
    {
        return $this->getData('method_name');
    }

    public function setMethodName($value)
    {
        return $this->setData('method_name', $value);
    }

    public function getMethodCode()
    {
        return $this->getData('method_code');
    }

    public function setMethodCode($value)
    {
        return $this->setData('method_code', $value);
    }

    public function getCountryCode()
    {
        return $this->getData('country_code');
    }

    public function setCountryCode($value)
    {
        return $this->setData('country_code', $value);
    }

    public function getPostCode()
    {
        return $this->getData('post_code');
    }

    public function setPostCode($value)
    {
        return $this->setData('post_code', $value);
    }

    public function getWeightFrom()
    {
        return $this->getData('weight_from');
    }

    public function setWeightFrom($value)
    {
        return $this->setData('weight_from', $value);
    }

    public function getWeightTo()
    {
        return $this->getData('weight_to');
    }

    public function setWeightTo($value)
    {
        return $this->setData('weight_to', $value);
    }

    public function getPrice()
    {
        return $this->getData('price');
    }

    public function setPrice($value)
    {
        return $this->setData('price', $value);
    }
}
