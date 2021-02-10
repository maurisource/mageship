<?php
namespace Maurisource\MageShip\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Maurisource\MageShip\Model\ResourceModel\Rates\CollectionFactory;
use Maurisource\MageShip\Api\RatesRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

class MassDelete extends Action
{
    private $collectionFactory;
    private $filter;
    private $ratesRepository;

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

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $results = $collection->walk([$this->ratesRepository,'delete']);
        $deletedCounts = count($results);

        $this->messageManager->addSuccessMessage(
            __('%1 Record(s) have been deleted.', $deletedCounts)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('mageship');
    }
}
