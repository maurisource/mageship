<?php
namespace Maurisource\MageShip\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Maurisource\MageShip\Api\Data\RatesInterface;
use Maurisource\MageShip\Model\ResourceModel\Rates\CollectionFactory;
use Maurisource\MageShip\Api\RatesRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

class Delete extends Action
{
    protected $collectionFactory;
    protected $filter;
    protected $ratesRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        RatesRepositoryInterface $ratesRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->ratesRepository = $ratesRepository;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Maurisource_MageShip::rates');
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var RatesInterface $rates */
        $rates = $this->collectionFactory->create()
            ->addFieldToFilter('rates_id', $id)
            ->setPageSize(1)
            ->getFirstItem();

        if ($rates->getId()) {
            $this->ratesRepository->delete($rates);
            $this->messageManager->addSuccessMessage(
                __('Record with id (%1) has been deleted.', $id)
            );
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('mageship');
    }
}
