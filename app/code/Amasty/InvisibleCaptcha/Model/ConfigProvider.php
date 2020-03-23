<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_InvisibleCaptcha
 */


namespace Amasty\InvisibleCaptcha\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\Base\Model\ConfigProviderAbstract;
use Amasty\InvisibleCaptcha\Helper\Data;

/**
 * Class ConfigProvider
 */
class ConfigProvider extends ConfigProviderAbstract
{
    protected $pathPrefix = 'aminvisiblecaptcha/';

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        parent::__construct($scopeConfig);
        $this->helper = $helper;
    }

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const CONFIG_PATH_GENERAL_ENABLE_MODULE = 'general/enabledCaptcha';
    const CONFIG_PATH_GENERAL_CAPTCHA_VERSION = 'general/captchaVersion';
    const CONFIG_PATH_GENERAL_CAPTCHA_SCORE = 'general/captchaScore';
    const CONFIG_PATH_GENERAL_CAPTCHA_ERROR_MESSAGE = 'general/errorMessage';
    const CONFIG_PATH_GENERAL_SITE_KEY = 'general/captchaKey';
    const CONFIG_PATH_GENERAL_SECRET_KEY = 'general/captchaSecret';
    const CONFIG_PATH_GENERAL_BADGE_POSITION = 'general/badgePosition';
    const CONFIG_PATH_GENERAL_BADGE_THEME = 'general/badgeTheme';
    const CONFIG_PATH_GENERAL_LANGUAGE = 'general/captchaLanguage';

    const CONFIG_PATH_ADVANCED_WHITELIST_IP = 'advanced/ipWhiteList';
    const CONFIG_PATH_ADVANCED_URLS = 'advanced/captchaUrls';
    const CONFIG_PATH_ADVANCED_SELECTORS = 'advanced/captchaSelectors';
    const CONFIG_PATH_ADVANCED_ENABLE_FOR_GUESTS_ONLY = 'advanced/enabledCaptchaForGuestsOnly';
    /**#@-*/

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag(self::CONFIG_PATH_GENERAL_ENABLE_MODULE);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getCaptchaVersion($storeId = null)
    {
        return (int)$this->getValue(self::CONFIG_PATH_GENERAL_CAPTCHA_VERSION, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return float
     */
    public function getCaptchaScore($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_CAPTCHA_SCORE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getConfigErrorMessage($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_CAPTCHA_ERROR_MESSAGE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabledForGuestsOnly($storeId = null)
    {
        return (bool)$this->getValue(self::CONFIG_PATH_ADVANCED_ENABLE_FOR_GUESTS_ONLY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSiteKey($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_SITE_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSecretKey($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_SECRET_KEY, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getBadgePosition($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_BADGE_POSITION, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getBadgeTheme($storeId = null)
    {
        return $this->getValue(self::CONFIG_PATH_GENERAL_BADGE_THEME, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getLanguage($storeId = null)
    {
        $language = $this->getValue(self::CONFIG_PATH_GENERAL_LANGUAGE, $storeId);
        if ($language && 7 > mb_strlen($language)) {
            $language = '&hl=' . $language;
        } else {
            $language = '';
        }

        return $language;
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getWhiteIps($storeId = null)
    {
        $ips = trim($this->getValue(self::CONFIG_PATH_ADVANCED_WHITELIST_IP, $storeId));

        $ips = $ips ? $this->helper->stringValidationAndConvertToArray($ips) : [];

        return $ips;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getConfigSelectors($storeId = null)
    {
        return trim($this->getValue(self::CONFIG_PATH_ADVANCED_SELECTORS, $storeId));
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getConfigUrls($storeId = null)
    {
        return trim($this->getValue(self::CONFIG_PATH_ADVANCED_URLS, $storeId));
    }
}
