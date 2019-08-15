<?php

namespace Infomodus\Caship\Block\Adminhtml\Widget\Items\Grid\Column\Renderer;

class Qty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public $_config;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Infomodus\Caship\Helper\Config $config,
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

        return $row->getQtyMin()." - ".($row->getQtyMax() > 0?$row->getQtyMax():"&infin;");
    }
}