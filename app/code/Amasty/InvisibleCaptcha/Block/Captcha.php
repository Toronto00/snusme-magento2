<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_InvisibleCaptcha
 */


namespace Amasty\InvisibleCaptcha\Block;

use Magento\Framework\View\Element\Template;
use Amasty\InvisibleCaptcha\Model\ConfigProvider;
use Amasty\InvisibleCaptcha\Model\Captcha as CaptchaModel;

class Captcha extends Template
{
    /**
     * Captcha model instance
     *
     * @var CaptchaModel
     */
    private $captchaModel;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        CaptchaModel $captchaModel,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->captchaModel = $captchaModel;
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
    }

    /**
     * Return Captcha model
     *
     * @return CaptchaModel
     */
    public function getCaptcha()
    {
        return $this->captchaModel;
    }

    /**
     * @return ConfigProvider
     */
    public function getConfig()
    {
        return $this->configProvider;
    }

    /**
     * Produce and return block's html output
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }
        return parent::toHtml();
    }
}
