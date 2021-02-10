<?php

namespace Maurisource\MageShip\Api\Data;

interface RatesInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getCarrierCode();

    /**
     * @param string $value
     * @return $this
     */
    public function setCarrierCode($value);

    /**
     * @return string
     */
    public function getCarrierTitle();

    /**
     * @param string $value
     * @return $this
     */
    public function setCarrierTitle($value);

    /**
     * @return string
     */
    public function getMethodName();

    /**
     * @param string $value
     * @return $this
     */
    public function setMethodName($value);

    /**
     * @return string
     */
    public function getMethodCode();

    /**
     * @param string $value
     * @return $this
     */
    public function setMethodCode($value);

    /**
     * @return string
     */
    public function getCountryCode();

    /**
     * @param string $value
     * @return $this
     */
    public function setCountryCode($value);

    /**
     * @return string
     */
    public function getPostCode();

    /**
     * @param string $value
     * @return $this
     */
    public function setPostCode($value);

    /**
     * @return float
     */
    public function getWeightFrom();

    /**
     * @param float $value
     * @return $this
     */
    public function setWeightFrom($value);

    /**
     * @return float
     */
    public function getWeightTo();

    /**
     * @param float $value
     * @return $this
     */
    public function setWeightTo($value);

    /**
     * @return float
     */
    public function getPrice();

    /**
     *
     * @param float $value
     * @return $this
     */
    public function setPrice($value);
}
