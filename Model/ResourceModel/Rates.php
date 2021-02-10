<?php
namespace Maurisource\MageShip\Model\ResourceModel;

class Rates extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected function _construct()
    {
        $this->_init('mageship_rates', 'rates_id');
    }
}
