<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
namespace Infomodus\Upsap\Model\Config;
class ShippingSettingsLink extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Infomodus_Upsap::system/config/button/method.phtml');
    }

    public function getButtonHtml2()
    {
        return [
                'id' => 'carriers_upsap_button_method',
                'label' => __('Manage UPS Access Point shipping methods'),
                'onclick' => $this->getUrl("infomodus_upsap/items/index"),
            ];
    }

    /**
     * Return element html
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
}