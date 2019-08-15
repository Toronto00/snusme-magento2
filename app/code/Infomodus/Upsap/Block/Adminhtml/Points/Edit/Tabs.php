<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Upsap\Block\Adminhtml\Points\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('infomodus_upsap_points_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Point'));
    }
}
