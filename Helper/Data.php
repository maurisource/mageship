<?php
namespace Maurisource\MageShip\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Maurisource\MageShip\Model\ResourceModel\Rates\CollectionFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Maurisource\MageShip\Api\Data\RatesInterface;

class Data extends AbstractHelper
{
    private $collectionFactory;

    const PATH_TYPE = 'carriers/mageship/handling_type';
    const PATH_FEE = 'carriers/mageship/handling_fee';

    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @param float $shippingValue
     * @return int
     */
    public function getHandlingFeeValue($shippingValue)
    {
        $fee = $this->scopeConfig->getValue(self::PATH_TYPE, 'websites');

        if ($fee <= 0) {
            return 0;
        }

        $handlingType =  $this->scopeConfig->getValue(self::PATH_TYPE, 'websites');

        if ($handlingType === AbstractCarrier::HANDLING_TYPE_PERCENT) {
            $fee = ($fee / 100 ) * $shippingValue;
        }

        return $fee;
    }

    public function getFailOverRates(RateRequest $request)
    {
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('post_code', $request->getDestPostcode())
            ->addFieldToFilter(
                ['country_code', 'country_code'],
                [
                    ['eq' => $request->getDestCountryId()],
                    ['eq' => '']
                ]
            );

        $weight = $request->getPackageWeight();

        $collection->setPackageWeightFilter($weight);

        /** @var RatesInterface[] $rates */
        $rates = $collection->getItems();

        $methods = [];

        foreach ($rates as $rate) {
            $methods[] = [
                'carrier' => $rate->getCarrierCode(),
                'carrier_title' => $rate->getCarrierTitle(),
                'name' => $rate->getMethodName(),
                'method' => $rate->getMethodCode(),
                'price' => $rate->getPrice()
            ];
        }

        return $methods;
    }
}
