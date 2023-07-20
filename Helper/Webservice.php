<?php

namespace Maurisource\MageShip\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Maurisource\MageShip\Model\Cache\Type\MageShip as CacheType;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\HTTP\Client\Curl;

class Webservice extends AbstractHelper
{
    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var CacheType
     */
    private $cache;

    /**
     * @var
     */
    private $carriers;

    /**
     * @var
     */
    private $urlApi;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var
     */
    private $lastErrorMessage;

    /**
     * @var Json
     */
    public $jsonSerialize;

    const CACHE_KEY = 'mageship_cache_key';
    const CACHE_URL_KEY = 'mageship_url_cache_key';
    const MODULE_NAME = 'Maurisource_MageShip';

    public function __construct(
        Context $context,
        Curl $curl,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        CacheType $cache,
        Json $jsonSerialize
    ) {
        $this->curl = $curl;
        $this->cache = $cache;
        $this->moduleList = $moduleList;
        $this->storeManager = $storeManager;
        $this->jsonSerialize = $jsonSerialize;
        parent::__construct($context);
    }

    public function getCarriers()
    {
        if ($this->carriers === null) {
            $apiKey = $this->getApiKey();

            if (empty($apiKey)) {
                return [];
            }

            $apiUrl = $this->getApiUrl($apiKey);

            if (empty($apiUrl)) {
                return [];
            }

            $headers = ['Content-Type' => 'application/json', 'api-key' => $apiKey];
            $this->curl->setHeaders($headers);
            $this->curl->get($apiUrl . '/carriers');
            $response = $this->curl->getBody();

            $this->carriers = json_decode($response, true);
        }

        return $this->carriers;
    }

    public function getEstimates($info)
    {
        $apiKey = $this->getApiKey();

        if (empty($apiKey)) {
            return [];
        }

        $carrierIds = $this->getAllowedCarriers();

        if (empty($carrierIds)) {
            return [];
        }

        $info['carrier_ids'] = $carrierIds;
        $sha1 = sha1(json_encode($info));
        $cacheKey = self::CACHE_KEY . '_' . $sha1;
        $response = $this->cache->load($cacheKey);

        if ($response === false) {
            $apiUrl = $this->getApiUrl($apiKey);

            if (empty($apiUrl)) {
                return [];
            }

            $headers = ['Content-Type' => 'application/json', 'api-key' => $apiKey];
            $this->curl->setHeaders($headers);
            $params = json_encode($info);
            $this->curl->post($apiUrl . '/rates/estimate', $params);
            $response = $this->curl->getBody();
            $this->cache->save($response, $cacheKey, [], 60 * 5);
        }

        return json_decode($response, true);
    }

    public function getAllowedCarriers()
    {
        $carriers = $this->scopeConfig->getValue('shipping/mageship/carriers');
        if ($carriers) {
            $carrierIds = explode(',', $carriers);
        } else {
            $carrierIds = [];
        }
        return $carrierIds;
    }

    /**
     * @param $apiKey
     * @return string
     */
    private function getApiUrl($apiKey)
    {
        if (!isset($this->urlApi)) {
            $sha1 = sha1($apiKey);
            $cacheKey = self::CACHE_URL_KEY . '_' . $sha1;
            $url = $this->cache->load($cacheKey, true);

            if ($url === false) {
                $url = $this->getApiRemoteUrl($apiKey);
                $this->cache->save($url, $cacheKey, [], 24 * 60 * 60, true);
            }

            $this->urlApi = $url;
        }

        return $this->urlApi;
    }

    private function getApiRemoteUrl($apiKey)
    {
        $mageShipApiUrl = 'http://mageship.io/rest/all/V1/authmageship/api/mage';
        $postParameters = [
            'client_store_url' => $this->getStoreUrl(),
            'client_module_version' => $this->getVersion()
        ];

        $jsonParameters = json_encode($postParameters);

        $headers = ['Content-Type' => 'application/json', 'api-key' => $apiKey];
        $this->curl->setHeaders($headers);
        $this->curl->post($mageShipApiUrl, $jsonParameters);
        $response = $this->curl->getBody();
        $url = '';
        $this->lastErrorMessage = '';

        if ($response) {
            $responseArray = $this->jsonSerialize->unserialize($response);
            if ($responseArray && isset($responseArray[0])) {
                $responseArray = $responseArray[0];
                if (isset($responseArray['url'])) {
                    $url = $responseArray['url'];
                } elseif (isset($responseArray['message'])) {
                    $this->lastErrorMessage = $responseArray['message'];
                }
            }
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    /**
     * @param $apiKey
     * @return bool
     */
    public function validateApiKey($apiKey)
    {
        $url = $this->getApiRemoteUrl($apiKey);

        return !empty($url);
    }

    private function getVersion()
    {
        return $this->moduleList
            ->getOne(self::MODULE_NAME)['setup_version'];
    }

    private function getStoreUrl()
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    private function getApiKey()
    {
        return $this->scopeConfig->getValue('shipping/mageship/apikey');
    }
}
