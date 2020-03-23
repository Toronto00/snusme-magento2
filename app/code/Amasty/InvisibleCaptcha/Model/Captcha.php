<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_InvisibleCaptcha
 */


namespace Amasty\InvisibleCaptcha\Model;

use Amasty\InvisibleCaptcha\Model\Config\Source\CaptchaVersion;

class Captcha
{
    /**
     * Google URL for checking captcha response
     */
    const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Config path to Amasty extensions
     */
    const CONFIG_PATH_AMASTY = 'aminvisiblecaptcha/amasty/';

    /**
     * Amasty extension URLs to validate
     *
     * @var array
     */
    private $additionalURLs = [];

    /**
     * Amasty extension form selectors
     *
     * @var array
     */
    private $additionalSelectors = [];

    /**
     * @var \Amasty\InvisibleCaptcha\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\HTTP\Adapter\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $sessionFactory;

    /**
     * @var \Amasty\Base\Model\GetCustomerIp
     */
    private $getCustomerIp;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * Captcha constructor
     *
     * @param \Amasty\InvisibleCaptcha\Helper\Data      $helper
     * @param \Magento\Framework\HTTP\Adapter\Curl      $curl
     * @param \Magento\Framework\Module\Manager         $moduleManager
     * @param \Magento\Framework\DataObject             $extensionsData
     * @param \Magento\Customer\Model\SessionFactory    $sessionFactory
     * @param \Amasty\Base\Model\GetCustomerIp          $getCustomerIp
     */
    public function __construct(
        \Amasty\InvisibleCaptcha\Helper\Data $helper,
        \Magento\Framework\HTTP\Adapter\Curl $curl,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\DataObject $extensionsData,
        \Magento\Customer\Model\SessionFactory $sessionFactory,
        \Amasty\Base\Model\GetCustomerIp $getCustomerIp,
        \Amasty\InvisibleCaptcha\Model\ConfigProvider $configProvider
    ) {
        $this->helper = $helper;
        $this->curl = $curl;
        $this->moduleManager = $moduleManager;
        $this->sessionFactory = $sessionFactory;
        $this->getCustomerIp = $getCustomerIp;
        $this->configProvider = $configProvider;
        foreach ($extensionsData->getData() as $configId => $data) {
            $isSettingEnabled = $this->helper->getConfigValueByPath(
                self::CONFIG_PATH_AMASTY . $configId,
                null,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($isSettingEnabled
                && $this->moduleManager->isEnabled($data['name'])
            ) {
                $this->additionalURLs[] = $data['url'];
                $this->additionalSelectors[] = $data['selector'];
            }
        }
    }

    /**
     * Check is need to show captcha
     *
     * @return bool
     */
    public function isNeedToShowCaptcha()
    {
        $session = $this->sessionFactory->create();

        if ($this->configProvider->isEnabled()) {
            if ($this->configProvider->isEnabledForGuestsOnly() && !$session->isLoggedIn()
                || !$this->configProvider->isEnabledForGuestsOnly()
            ) {
                if (!in_array($this->getCustomerIp->getCurrentIp(), $this->configProvider->getWhiteIps())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Form selectors getter
     *
     * @return string
     */
    public function getSelectors()
    {
        $selectors = $this->configProvider->getConfigSelectors();

        $selectors = $selectors ? $this->helper->stringValidationAndConvertToArray($selectors) : [];

        return implode(',', array_merge($selectors, $this->additionalSelectors));
    }

    /**
     * URLs to validate getter
     *
     * @return array
     */
    public function getUrls()
    {
        $urls = $this->configProvider->getConfigUrls();

        $urls = $urls ? $this->helper->stringValidationAndConvertToArray($urls) : [];

        return array_merge($urls, $this->additionalURLs);
    }

    /**
     * Verification of token by Google
     *
     * @param string $token
     * @return array
     */
    public function verify($token)
    {
        $verification = [
            'success' => false,
            'error' => __('No reCaptcha token.')
        ];
        if ($token) {
            $curlParams = [
                'secret' => $this->configProvider->getSecretKey(),
                'response' => $token
            ];

            try {
                $this->curl->write(
                    \Zend_Http_Client::POST,
                    self::GOOGLE_VERIFY_URL,
                    '1.1',
                    [],
                    $curlParams
                );
                $googleResponse = $this->curl->read();
                $responseBody = \Zend_Http_Response::extractBody($googleResponse);
                $googleAnswer = \Zend_Json::decode($responseBody);
                if (array_key_exists('success', $googleAnswer)) {
                    if (isset($googleAnswer['score'])
                        && $this->configProvider->getCaptchaVersion() === CaptchaVersion::VERSION_3
                        && $googleAnswer['score'] < $this->configProvider->getCaptchaScore()
                    ) {
                        $verification['error'] = $this->configProvider->getConfigErrorMessage();
                        $verification['success'] = false;
                    } elseif ($googleAnswer['success']) {
                        $verification['success'] = true;
                    } elseif (array_key_exists('error-codes', $googleAnswer)) {
                        $verification['error'] = $this->getErrorMessage($googleAnswer['error-codes'][0]);
                    }
                }
            } catch (\Exception $e) {
                $verification['error'] = __($e->getMessage());
            }
        }

        return $verification;
    }

    private function getErrorMessage($errorCode)
    {
        $errorCodesGoogle = [
            'missing-input-secret' => __('The secret parameter is missing.'),
            'invalid-input-secret' => __('The secret parameter is invalid or malformed.'),
            'missing-input-response' => __('The response parameter is missing.'),
            'invalid-input-response' => __('The response parameter is invalid or malformed.'),
            'bad-request' => __('The request is invalid or malformed.')
        ];

        if (array_key_exists($errorCode, $errorCodesGoogle)) {
            return $errorCodesGoogle[$errorCode];
        }

        return __('Something is wrong.');
    }
}
