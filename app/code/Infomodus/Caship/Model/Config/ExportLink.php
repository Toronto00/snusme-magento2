<?php
/**
 * Created by PhpStorm.
 * User: Vitalij
 * Date: 01.10.14
 * Time: 11:55
 */
namespace Infomodus\Caship\Model\Config;
class ExportLink extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Infomodus_Caship::system/config/button/export.phtml');
    }
    public function getCashipExportButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'carriers_caship_button_export',
                'label' => __('Export'),
                'onclick' => 'setLocation(\''.$this->getUrl("infomodus_caship/items/export").'\')',
            ]
        );
        return $button->toHtml();
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