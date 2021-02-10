<?php
namespace Maurisource\MageShip\Model\ResourceModel\Rates;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Maurisource\MageShip\Model\Rates', 'Maurisource\MageShip\Model\ResourceModel\Rates');
        $this->_setIdFieldName('rates_id');
    }

    public function setPackageWeightFilter($fromWeight, $toWeight = null)
    {
        if ($toWeight === null) {
             $toWeight = $fromWeight;
        }

        $weightWhere = "
        (
              main_table.`weight_from` = 0
              AND main_table.`weight_to` = 0
            )
            OR (
              main_table.`weight_from` = 0
              AND main_table.`weight_to` >= '$toWeight'
            )
            OR (
              main_table.`weight_from` <= '$fromWeight'
              AND main_table.`weight_to` = 0
            )
            OR (
              main_table.`weight_from` <= '$fromWeight'
              AND main_table.`weight_to` >= '$toWeight'
            )
        ";

        $this->getSelect()->where($weightWhere);
    }
}
