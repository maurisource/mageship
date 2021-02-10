<?php
namespace Maurisource\MageShip\Model\Rate;

class Result extends \Magento\Shipping\Model\Rate\Result
{

    /**
     * {@inheritdoc }
     */
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || empty($this->_rates)) {
            return $this;
        }

        /* @var \Magento\Quote\Model\Quote\Address\RateResult\Method $rate */
        foreach ($this->_rates as $i => $rate) {
            $tmp[$i] = $rate->getPrice();
        }

        natsort($tmp);
        $result = [];
        $results = [];

        foreach ($tmp as $i => $price) {
            $rate = $this->_rates[$i];
            $method = explode('_', $rate->getMethod(), 2);
            $carrierId = $method[0];
            $results[$carrierId][] = $rate;
        }

        foreach ($results as $carrierResult) {
            $result = array_merge($result, $carrierResult);
        }

        $this->_rates = $result;
        return $this;
    }
}
