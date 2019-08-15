<?php

namespace Infomodus\Upsap\Block\Adminhtml\Widget\Points\Grid\Column\Renderer;

class StoreId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $txt = [];
        if($row->getIsStoreAll() == 1) {
            $ids = explode(',', $row->getStoreId());
            $storesModel = $this->_config->getModel()->get('Infomodus\Upsap\Model\Config\Stores');
            foreach ($ids as $id) {
                $txt[] = $storesModel->getStoreNameById($id);
            }
        } else {
            return __('All');
        }

        return implode('<br>', $txt);
    }
}