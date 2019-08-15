<?php

namespace Infomodus\Upsap\Block\Adminhtml\Widget\Points\Grid\Column\Renderer;

class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $_config;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Infomodus\Upsap\Helper\Config $config,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    public function setColumn($column)
    {
        parent::setColumn($column);
        return $this;
    }

    /**
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $address = json_decode($row->getAddress(), true);
        $addressForm = "<u>" . $address['name'] . "</u>: <br>";
        $addressForm .= $address['addLine1'] . " ";
        if (isset($address['addLine2'])) {
            $addressForm .= $address['addLine2'] . " ";
        }
        if (isset($address['addLine3'])) {
            $addressForm .= $address['addLine3'] . " ";
        }

        $addressForm .= $address['city'] . " ";

        if (isset($address['state'])) {
            $addressForm .= $address['state'] . " ";
        }

        if (isset($address['postal'])) {
            $addressForm .= $address['postal'] . " ";
        }

        $addressForm .= $this->_config->getModel()->get('Magento\Directory\Model\Country')->loadByCode($address['country'])->getName() . " ";

        return $addressForm;
    }
}