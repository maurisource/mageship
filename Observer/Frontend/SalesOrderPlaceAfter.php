<?php
namespace Maurisource\MageShip\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Maurisource\MageShip\Model\ResourceModel\Rates\CollectionFactory as RateFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory;
use Maurisource\MageShip\Api\RatesRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;

class SalesOrderPlaceAfter implements ObserverInterface
{
    private $collectionFactory;
    private $rateFactory;
    private $rateRepository;
    private $quoteRepository;

    public function __construct(
        CollectionFactory $collectionFactory,
        RateFactory $rateFactory,
        RatesRepositoryInterface $rateRepository,
        QuoteRepository $quoteRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->rateFactory = $rateFactory;
        $this->rateRepository = $rateRepository;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        $shippingMethodObject = $order->getShippingMethod(true);

        if ($shippingMethodObject->getCarrierCode() != \Maurisource\MageShip\Model\Carrier::CARRIER_CODE) {
            return $this;
        }

        if ($shippingMethodObject->getMethod() == \Maurisource\MageShip\Model\Carrier::DEFAULT_METHOD) {
            return $this;
        }

        $quoteId = $order->getQuoteId();

        if ($quoteId === null) {
            return $this;
        }

        $quote = $this->quoteRepository->get($quoteId);

        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress === null) {
            return $this;
        }

        $shippingMethod = $order->getShippingMethod();
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod);

        if (!$shippingRate->getId()) {
            return $this;
        }

        $rateArray = [
            'carrier_code' => $shippingRate->getCarrier(),
            'carrier_title' => $shippingRate->getCarrierTitle(),
            'method_name' => $shippingRate->getMethodTitle(),
            'method_code' => $shippingRate->getMethod(),
            'country_code' => $shippingAddress->getCountryId(),
            'post_code' => $shippingAddress->getPostcode(),
            'weight_from' => $shippingAddress->getWeight(),
            'weight_to' => $shippingAddress->getWeight(),
            'price' => $shippingAddress->getShippingAmount(),
        ];

        /** @var \Maurisource\MageShip\Api\Data\RatesInterface $mageshipRate */
        $mageshipRate = $this->rateFactory->create()
            ->addFieldToFilter('post_code', $rateArray['post_code'])
            ->addFieldToFilter('country_code', $rateArray['country_code'])
            ->addFieldToFilter('weight_from', $rateArray['weight_from'])
            ->addFieldToFilter('weight_to', $rateArray['weight_to'])
            ->addFieldToFilter('carrier_code', $rateArray['carrier_code'])
            ->addFieldToFilter('carrier_title', $rateArray['carrier_title'])
            ->addFieldToFilter('method_code', $rateArray['method_code'])
            ->addFieldToFilter('method_name', $rateArray['method_name'])
            ->setPageSize(1)
            ->getFirstItem();

        if ($mageshipRate->getPrice() != $rateArray['price']) {
            try {
                $mageshipRate->setData($rateArray);
                $this->rateRepository->save($mageshipRate);
            } catch (\Exception $e) {
            }
        }

        return $this;
    }
}
