<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */
namespace Infomodus\Upslabel\Block\Adminhtml\Address\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('infomodus_address_form');
        $this->setTitle(__('Address Information'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('infomodus_upslabel/address/save'),
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
