<?php
namespace Maurisource\MageShip\Model;

use Maurisource\MageShip\Api\RatesRepositoryInterface;

class RatesRepository implements RatesRepositoryInterface
{
    private $resourceModel;

    public function __construct(\Maurisource\MageShip\Model\ResourceModel\Rates $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    public function save(\Maurisource\MageShip\Api\Data\RatesInterface $rates)
    {
        try {
            $this->resourceModel->save($rates);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to save rates'), $e);
        }
    }

    public function delete(\Maurisource\MageShip\Api\Data\RatesInterface $rates)
    {
        try {
            $this->resourceModel->delete($rates);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to delete rates'), $e);
        }
    }
}
