<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Upsap\Block\Adminhtml\Points\Edit\Tab;


use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;


class Main extends Generic implements TabInterface
{
    protected $_conf;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Infomodus\Upsap\Helper\Config $config,
        array $data = []
    )
    {
        $this->_conf = $config;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Access Point Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Access Point Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_infomodus_upsap_points');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'item_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Access Point Information')]);
        $modelData = $model->getData();
        $form->setValues($modelData);

        $fieldset->addField('order_increment_id', 'text', array(
            'name' => 'order_increment_id',
            'label' => __('Order #'),
            'value' => $modelData['order_increment_id']
        ));

        $fieldset->addField('appu_id', 'text', array(
            'name' => 'appu_id',
            'label' => __('Access Point Id'),
            'value' => $modelData['appu_id']
        ));

        $fieldset->addField('address', 'textarea', array(
            'name' => 'address',
            'label' => __('Address'),
            'value' => $this->parseAddress($modelData['address']),
            'rows' => 8
        ));


        $this->setForm($form);
        return parent::_prepareForm();
    }

    private function parseAddress($address)
    {
        $address = json_decode($address, true);
        $addressForm = $address['name'] . ": 
     ";
        $addressForm .= $address['addLine1'] . " ";
        if (isset($address['addLine2'])) {
            $addressForm .= $address['addLine2'] . " ";
        }
        if (isset($address['addLine3'])) {
            $addressForm .= $address['addLine3'] . " ";
        }

        $addressForm .= " 
     ";

        $addressForm .= $address['city'] . " 
     ";

        if (isset($address['state'])) {
            $addressForm .= $address['state'] . " 
     ";
        }

        if (isset($address['postal'])) {
            $addressForm .= $address['postal'] . " 
     ";
        }

        $addressForm .= $this->_conf->getModel()->get('Magento\Directory\Model\Country')->loadByCode($address['country'])->getName() . " ";

        return $addressForm;
    }
}
