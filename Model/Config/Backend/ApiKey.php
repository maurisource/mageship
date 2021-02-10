<?php

namespace Maurisource\MageShip\Model\Config\Backend;

class ApiKey extends \Magento\Framework\App\Config\Value
{
    private $webserviceHelper;
    private $messageManager;

    public function __construct(
        \Maurisource\MageShip\Helper\Webservice $webserviceHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->webserviceHelper = $webserviceHelper;
        $this->messageManager = $messageManager;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $apiKey = $this->getValue();

        if ($apiKey != '') {
            $validationFlag = $this->webserviceHelper->validateApiKey($apiKey);
            $errorMessage = __('API Key is invalid');

            if ($validationFlag === false) {
                $lastErrorMessage = $this->webserviceHelper->getLastErrorMessage();

                if (!empty($lastErrorMessage)) {
                    $errorMessage = $lastErrorMessage;
                }

                $this->messageManager->addErrorMessage($errorMessage);
            } else {
                $this->messageManager->addSuccessMessage(__('API Key is valid'));
            }
        } else {
            $this->messageManager->addWarningMessage(__('You need an API Key for the feature to work'));
        }

        parent::beforeSave();
    }
}
