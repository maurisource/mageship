<?php
namespace Maurisource\MageShip\Api;

interface RatesRepositoryInterface
{
    /**
     * @return RatesRepositoryInterface
     */
    public function save(\Maurisource\MageShip\Api\Data\RatesInterface $rates);

    /**
     * @return RatesRepositoryInterface
     */
    public function delete(\Maurisource\MageShip\Api\Data\RatesInterface $rates);
}
