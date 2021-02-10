<?php
namespace Maurisource\MageShip\Model\Config\Source;

class Methods extends Carriers
{
    public function toOptionArray()
    {
        $allowedCarriers = $this->getWebserviceHelper()->getAllowedCarriers();

        if (empty($allowedCarriers)) {
            return [];
        }

        $optionArray = [];
        $carrierRaw = $this->getWebserviceHelper()->getCarriers();

        if (isset($carrierRaw['carriers'])) {
            $carriers = $carrierRaw['carriers'];

            foreach ($carriers as $carrier) {
                if (!in_array($carrier['carrier_id'], $allowedCarriers)) {
                    continue;
                }

                if (isset($carrier['services'])) {
                    foreach ($carrier['services'] as $service) {
                        $value = $service['carrier_id'] . '_' . $service['service_code'];
                        $label = $carrier['friendly_name'] . ': ' . $service['name'];
                        $optionArray[] = ['value' => $value, 'label' => $label];
                    }
                }
            }
        }

        return $optionArray;
    }
}
