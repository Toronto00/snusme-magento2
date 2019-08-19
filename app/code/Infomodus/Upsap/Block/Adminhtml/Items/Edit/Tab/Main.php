<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Upsap\Block\Adminhtml\Items\Edit\Tab;


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
        return __('Method Information');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Method Information');
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
        $model = $this->_coreRegistry->registry('current_infomodus_upsap_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'item_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Method Information')]);
        if ($model->getId()) {
            $fieldset->addField('upsapshippingmethod_id', 'hidden', ['name' => 'upsapshippingmethod_id']);
        }

        $fieldset->addField('title', 'text', [
            'name' => 'title',
            'label' => __('Title'),
            'title' => __('Title'),
            'required' => true,
            'after_element_html' => '<p class="nm"><small>' . __('It appears only in admin interface') . '</small></p>',
        ]);

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Method name'),
            'title' => __('Method name'),
            'required' => true,
        ]);

        $fieldset->addField('upsmethod_id', 'select', [
            'name' => 'upsmethod_id',
            'label' => __('UPS Shipping method'),
            'title' => __('UPS Shipping method'),
            'required' => true,
            'values' => $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\Upsmethod')->toOptionArray()
        ]);

        $fieldset->addField('dinamic_price', 'select', [
            'name' => 'dinamic_price',
            'label' => __('Price Source'),
            'title' => __('Price Source'),
            'values' => $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\DinamicPrice')->toOptionArray(),
        ]);

        $fieldset->addField('price', 'text', [
            'name' => 'price',
            'label' => __('Price'),
            'title' => __('Price'),
            'style' => 'border-width: 1px;',
        ]);

        $fieldset->addField('added_value_type', 'select', [
            'name' => 'added_value_type',
            'label' => __('Add extra price type'),
            'title' => __('Add extra price type'),
            'values' => $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\AddedValueType')->toOptionArray(),
        ]);

        $fieldset->addField('added_value', 'text', [
            'name' => 'added_value',
            'label' => __('Add extra value'),
            'title' => __('Add extra value'),
        ]);

        $fieldset->addField('negotiated', 'select', [
            'name' => 'negotiated',
            'label' => __('Negotiated rates'),
            'title' => __('Negotiated rates'),
            'values' => $this->_conf->_objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toOptionArray(),
        ]);

        $fieldset->addField('negotiated_amount_from', 'text', [
            'name' => 'negotiated_amount_from',
            'label' => __('Order amount from for Negotiated rates'),
            'title' => __('Order amount from for Negotiated rates'),
            'value' => 0,
        ]);

        $fieldset->addField('time_in_transit', 'select', [
            'name' => 'time_in_transit',
            'label' => __('Time in transit'),
            'title' => __('Time in transit'),
            'values' => $this->_conf->_objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toOptionArray(),
        ]);

        $fieldset->addField('tit_show_format', 'select', [
            'name' => 'tit_show_format',
            'label' => __('Time in transit format'),
            'title' => __('Time in transit format'),
            'options' => ['days' => '1 day(s)', 'datetime' => '09 July 2016 10:30', 'full' => 'Friday, February 22, 2019']
        ]);

        $fieldset->addField('tit_close_hour', 'select', [
            'name' => 'tit_close_hour',
            'label' => __('Close Hour'),
            'title' => __('Close Hour'),
            'options' => array('23:00' => 23, '22:00' => 22, '21:00' => 21, '20:00' => 20, '19:00' => 19,
                '18:00' => 18, '17:00' => 17, '16:00' => 16, '15:00' => 15, '14:00' => 14, '13:00' => 13,
                '12:00' => 12, '11:00' => 11, '10:00' => 10, '09:00' => 9, '08:00' => 8, '07:00' => 7,
                '06:00' => 6, '05:00' => 5, '04:00' => 4, '03:00' => 3, '02:00' => 2, '01:00' => 1, '24:00' => __('Always ready')),
        ]);

        $fieldset->addField('add_day', 'text', [
            'name' => 'add_day',
            'label' => __('Add day'),
            'title' => __('Add day'),
            'value' => 0,
        ]);

        $fieldset->addField('amount_min', 'text', [
            'name' => 'amount_min',
            'label' => __('Minimum Order Subtotal'),
            'title' => __('Minimum Order Subtotal'),
            'value' => 0,
        ]);
        $fieldset->addField('amount_max', 'text', [
            'name' => 'amount_max',
            'label' => __('Maximum Order Subtotal'),
            'title' => __('Maximum Order Subtotal'),
            'value' => 0,
            'after_element_html' => '<p class="note"><span>' . __('If 0 then infinity') . '</span></p>',
        ]);
        $fieldset->addField('weight_min', 'text', [
            'name' => 'weight_min',
            'label' => __('Minimum Order Weight'),
            'title' => __('Minimum Order Weight'),
            'value' => 0,
        ]);
        $fieldset->addField('weight_max', 'text', [
            'name' => 'weight_max',
            'label' => __('Maximum Order Weight'),
            'title' => __('Maximum Order Weight'),
            'value' => 0,
            'after_element_html' => '<p class="note"><span>' . __('If 0 then infinity') . '</span></p>',
        ]);
        $fieldset->addField('qty_min', 'text', [
            'name' => 'qty_min',
            'label' => __('Minimum Product Quantity'),
            'title' => __('Minimum Product Quantity'),
            'value' => 0,
        ]);
        $fieldset->addField('qty_max', 'text', [
            'name' => 'qty_max',
            'label' => __('Maximum Product Quantity'),
            'title' => __('Maximum Product Quantity'),
            'value' => 0,
            'after_element_html' => '<p class="note"><span>' . __('If 0 then infinity') . '</span></p>',
        ]);
        $fieldset->addField('zip_min', 'text', [
            'name' => 'zip_min',
            'label' => __('Minimum ZIP/Postal code'),
            'title' => __('Minimum ZIP/Postal code'),
            'value' => "",
        ]);
        $fieldset->addField('zip_max', 'text', [
            'name' => 'zip_max',
            'label' => __('Maximum ZIP/Postal code'),
            'title' => __('Maximum ZIP/Postal code'),
            'value' => "",
        ]);
        $fieldset->addField('tax', 'select', [
            'name' => 'tax',
            'label' => __('Duty and Tax'),
            'title' => __('Duty and Tax'),
            'values' => $this->_conf->_objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toOptionArray(),
        ]);

        $fieldset->addField('country_ids', 'multiselect', [
            'name' => 'country_ids',
            'label' => __('Allowed Countries'),
            'title' => __('Allowed Countries'),
            'required' => true,
            'values' => $this->_conf->_objectManager->get('Magento\Directory\Model\Config\Source\Country')->toOptionArray(true),
        ]);

        $fieldset->addField('user_group_ids', 'multiselect', [
            'name' => 'user_group_ids',
            'label' => __('User groups'),
            'title' => __('User groups'),
            'values' => $this->_conf->_objectManager->get('Magento\Customer\Model\ResourceModel\Group\Collection')->toOptionArray(),
        ]);

        $fieldset->addField('is_prod_allow', 'select', [
            'name' => 'is_prod_allow',
            'label' => __('Disable by with Product Attribute'),
            'values' => $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\ProductAttributes')->toOptionArray(),
        ]);

        $fieldset->addField('is_store_all', 'select', [
            'name' => 'is_store_all',
            'label' => __('Apply to Store'),
            'values' => $this->_conf->_objectManager->get('Infomodus\Upsap\Model\Config\Specific')->toOptionArray(),
        ]);

        $fieldset->addField('store_id', 'multiselect', [
            'name' => 'store_id',
            'required' => true,
            'label' => __('Allowed Stores'),
            'values' => $this->_conf->getStoresOptionsArray(),
        ]);

        $fieldset->addField('free_shipping', 'select', [
            'name' => 'free_shipping',
            'label' => __('Free Shipping'),
            'title' => __('Free Shipping'),
            'values' => $this->_conf->_objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toOptionArray(),
        ]);
        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Enabled'),
            'title' => __('Enabled'),
            'values' => $this->_conf->_objectManager->get('Magento\Config\Model\Config\Source\Yesno')->toOptionArray(),
        ]);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}dinamic_price",
                'dinamic_price'
            )->addFieldMap(
                "{$htmlIdPrefix}price",
                'price'
            )->addFieldMap(
                "{$htmlIdPrefix}negotiated",
                'negotiated'
            )->addFieldMap(
                "{$htmlIdPrefix}negotiated_amount_from",
                'negotiated_amount_from'
            )->addFieldMap(
                "{$htmlIdPrefix}time_in_transit",
                'time_in_transit'
            )->addFieldMap(
                "{$htmlIdPrefix}tit_show_format",
                'tit_show_format'
            )->addFieldMap(
                "{$htmlIdPrefix}tit_close_hour",
                'tit_close_hour'
            )->addFieldMap(
                "{$htmlIdPrefix}add_day",
                'add_day'
            )->addFieldMap(
                "{$htmlIdPrefix}added_value_type",
                'added_value_type'
            )->addFieldMap(
                "{$htmlIdPrefix}added_value",
                'added_value'
            )->addFieldMap(
                "{$htmlIdPrefix}is_store_all",
                'is_store_all'
            )->addFieldMap(
                "{$htmlIdPrefix}store_id",
                'store_id'
            )->addFieldDependence(
                'store_id',
                'is_store_all',
                '1'
            )->addFieldDependence(
                'price',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'added_value_type',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'added_value',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'negotiated',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'negotiated_amount_from',
                'negotiated',
                '1'
            )->addFieldDependence(
                'add_day',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'tit_close_hour',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'tit_show_format',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'add_day',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'tit_close_hour',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'time_in_transit',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'tit_show_format',
                'dinamic_price',
                '1'
            )
        );

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
