<?php
/**
 */

namespace Jacob\Checkout\Block\Adminhtml\System\Config\Field;

class Country extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * All possible countries
     *
     * @var array
     */
    protected $countries = [];

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $countrySource;

    /**
     * Countries constructor.
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Directory\Model\Config\Source\Country $countrySource
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Directory\Model\Config\Source\Country $countrySource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->countrySource = $countrySource;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCounties() as $country) {
                if (isset($country['value']) && $country['value'] && isset($country['label']) && $country['label']) {
                    $this->addOption($country['value'], $country['label']);
                }
            }
        }
        $this->setClass('cc-type-select');
        return parent::_toHtml();
    }

    /**
     * All countries
     *
     * @return array
     */
    protected function _getCounties()
    {
        if (!$this->countries) {
            $this->countries = $this->countrySource->toOptionArray();
        }
        return $this->countries;
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }
}
