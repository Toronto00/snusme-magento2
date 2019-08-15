<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Upsap\Block\Adminhtml;

class Points extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'points';
        $this->_headerText = __('Access Points');
        parent::_construct();
        $this->removeButton('add');
    }
}
