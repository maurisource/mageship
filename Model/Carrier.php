<?php
namespace Maurisource\MageShip\Model;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

class Carrier extends AbstractCarrier implements CarrierInterface
{
    private $rateResultFactory;
    private $rateMethodFactory;
    private $webserviceHelper;
    private $helper;
    private $directoryHelper;
    private $allowedMethods;

    const DEFAULT_METHOD = 'standard';
    const CARRIER_CODE = 'mageship';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Maurisource\MageShip\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Maurisource\MageShip\Helper\Webservice $webserviceHelper,
        \Maurisource\MageShip\Helper\Data $helper,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->webserviceHelper = $webserviceHelper;
        $this->helper = $helper;
        $this->directoryHelper = $directoryHelper;
        $this->_code = self::CARRIER_CODE;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return bool|\Magento\Shipping\Model\Rate\Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isApplicable($request)) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        $estimates = $this->getMethodsFromEstimates($request);

        foreach ($estimates as $estimate) {

            /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier($estimate['carrier']);
            $method->setCarrierTitle($estimate['carrier_title']);

            $method->setMethod($estimate['method']);
            $method->setMethodTitle($estimate['name']);

            $amount = $estimate['price'];

            $handlingFee = $this->helper->getHandlingFeeValue($amount);

            $amount += $handlingFee;

            $method->setPrice($amount);
            $method->setCost($amount);

            $result->append($method);
        }

        return $result;
    }

    /**
     * @param RateRequest $request
     * @return bool
     */
    private function isApplicable(RateRequest $request)
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        if (!$request->getDestCountryId() && !$request->getDestPostcode()
            && !$request->getDestCity() && !$request->getDestRegionCode()) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [];
    }

    private function getMethodsFromEstimates(RateRequest $request)
    {
        $methods = [];

        $info = [
            'from_country_code' => $request->getCountryId(),
            'from_postal_code' => $request->getPostcode(),
            'to_country_code' => $request->getDestCountryId(),
            'to_postal_code' => $request->getDestPostcode(),
            'to_city_locality' => $request->getDestCity(),
            'to_state_province' => $request->getDestRegionCode(),
            'weight' => ['value' => $request->getPackageWeight(), 'unit' => $this->getInternalWeightUnit()],
            'address_residential_indicator' => $this->getConfigFlag('residential') ? 'yes' : 'no'
        ];

        $estimates = $this->webserviceHelper->getEstimates($info);

        if (isset($estimates['errors'])) {

            $this->_logger->error(json_encode($estimates['errors'][0]['message']));

            return [];
        }

        $successMethods = false;

        foreach ($estimates as $estimate) {
            $carrierId = $estimate['carrier_id'];
            $carrierName = $estimate['carrier_friendly_name'];
            $method = $carrierId . '_' . $estimate['service_code'];

            if (!$this->isMethodAllowed($method)) {
                continue;
            }

            if ($estimate['package_type'] != '' && $estimate['package_type'] != 'package') {
                continue;
            }

            $methods[] = [
                'carrier' => $this->_code,
                'carrier_title' => $carrierName,
                'method' => $method,
                'name' => $estimate['service_type'],
                'price' => $this->getAmount($estimate)
            ];

            $successMethods = true;
        }

        if ($successMethods === false) {
            if ($this->getConfigFlag('failover')) {
                return $this->helper->getFailOverRates($request);
            }

            return $this->defaultMethod();
        }

        return $methods;
    }

    private function getInternalWeightUnit()
    {
        $storeWeightUnit = $this->directoryHelper->getWeightUnit();

        $unit = '';

        switch ($storeWeightUnit) {
            case 'lbs':
                $unit = 'pound';
                break;
            case 'kgs':
                $unit = 'kilogram';
                break;
        }

        return $unit;
    }

    private function defaultMethod()
    {
        $methods = [];

        $price = (float)$this->getConfigData('price');

        if ($price) {
            $methods[] = [
                'carrier' => $this->_code,
                'carrier_title' => $this->getConfigData('title'),
                'name' => 'Standard',
                'method' => self::DEFAULT_METHOD,
                'price' => $price
            ];
        }

        return $methods;
    }

    private function isMethodAllowed($method)
    {
        $allowedMethods = $this->_scopeConfig->getValue('shipping/mageship/allowed_methods');

        if ($allowedMethods) {
            if ($this->allowedMethods === null) {
                $this->allowedMethods = explode(',', $allowedMethods);
            }
            return in_array($method, $this->allowedMethods);
        }

        return false;
    }

    private function getAmount($estimate)
    {
        $shippingAmount = isset($estimate['shipping_amount']['amount'])?$estimate['shipping_amount']['amount']:0;
        $insuranceAmount = isset($estimate['insurance_amount']['amount'])?$estimate['insurance_amount']['amount']:0;
        $confirmationAmount = isset($estimate['insurance_amount']['amount'])?$estimate['insurance_amount']['amount']:0;
        $otherAmount = isset($estimate['other_amount']['amount'])?$estimate['other_amount']['amount']:0;

        return $shippingAmount + $insuranceAmount + $confirmationAmount + $otherAmount;
    }
}
