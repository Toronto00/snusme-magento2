<?php
/**
 */

namespace Jacob\Checkout\Block\Adminhtml\System\Config\Field;

class CountryInfo extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var \Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Country
     */
    protected $_countryRenderer = null;

    /**
     * Return renderer for installments
     *
     * @return Installment|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCountryRenderer()
    {
        if (!$this->_countryRenderer) {
            $this->_countryRenderer = $this->getLayout()->createBlock(
                '\Jacob\Checkout\Block\Adminhtml\System\Config\Field\Country',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->_countryRenderer;
    }

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'country',
            [
                'label'     => __('Country'),
                'renderer'  => $this->getCountryRenderer()
            ]
        );
        $this->addColumn(
            'info',
            [
                'label'     => __('Information'),
                'renderer'  => false,
                'style'      => 'width: 600px'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add country');
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];

        if ($country = $row->getCountry()) {
            $optionHash = 'option_' . $this->getCountryRenderer()->calcOptionHash($country);

            $options[$optionHash] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        return;
    }
}
