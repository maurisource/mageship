<?php

namespace Maurisource\MageShip\Model\Config\Source;

class Carriers implements \Magento\Framework\Option\ArrayInterface
{
    private $webserviceHelper;

    public function __construct(
        \Maurisource\MageShip\Helper\Webservice $webserviceHelper
    ) {
        $this->webserviceHelper = $webserviceHelper;
    }

    public function toOptionArray()
    {
        $optionArray = [];
        $carrierRaw = $this->webserviceHelper->getCarriers();

        if (isset($carrierRaw['carriers'])) {
            $carriers = $carrierRaw['carriers'];

            foreach ($carriers as $carrier) {
                $optionArray[] = ['value' => $carrier['carrier_id'], 'label' => $carrier['friendly_name']];
            }
        }

        return $optionArray;
    }

    public function getWebserviceHelper()
    {
        return $this->webserviceHelper;
    }
}
