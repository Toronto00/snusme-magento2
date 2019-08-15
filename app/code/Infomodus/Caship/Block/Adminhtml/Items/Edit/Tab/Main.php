<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Infomodus\Caship\Block\Adminhtml\Items\Edit\Tab;


use Infomodus\Caship\Helper\Config;
use Infomodus\Caship\Model\Config\AddedValueType;
use Infomodus\Caship\Model\Config\DynamicPrice;
use Infomodus\Caship\Model\Config\ProductAttributes;
use Infomodus\Caship\Model\Config\ShippingCompany;
use Infomodus\Caship\Model\Config\ShippingCompanyAll;
use Infomodus\Caship\Model\Config\ShippingMethods;
use Infomodus\Caship\Model\Config\Specific;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Shipping\Model\Config\Source\Allspecificcountries;


class Main extends Generic implements TabInterface
{
    protected $_conf;
    protected $_fieldFactory;
    protected $shippingMethods;
    protected $dynamicPrice;
    protected $shippingCompany;
    protected $shippingCompanyAll;
    protected $addedValueType;
    protected $yesno;
    protected $allSpecificCountries;
    protected $country;
    protected $customerCollection;
    protected $productAttributes;
    protected $specific;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $config,
        FieldFactory $fieldFactory,
        ShippingMethods $shippingMethods,
        DynamicPrice $dynamicPrice,
        ShippingCompany $shippingCompany,
        ShippingCompanyAll $shippingCompanyAll,
        AddedValueType $addedValueType,
        Yesno $yesno,
        Allspecificcountries $allSpecificCountries,
        Country $country,
        Collection $customerCollection,
        ProductAttributes $productAttributes,
        Specific $specific,
        array $data = []
    )
    {
        $this->_conf = $config;
        $this->_fieldFactory = $fieldFactory;
        $this->shippingMethods = $shippingMethods;
        $this->dynamicPrice = $dynamicPrice;
        $this->shippingCompany = $shippingCompany;
        $this->shippingCompanyAll = $shippingCompanyAll;
        $this->addedValueType = $addedValueType;
        $this->yesno = $yesno;
        $this->allSpecificCountries = $allSpecificCountries;
        $this->country = $country;
        $this->customerCollection = $customerCollection;
        $this->productAttributes = $productAttributes;
        $this->specific = $specific;
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
        $model = $this->_coreRegistry->registry('current_infomodus_caship_items');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $htmlIdPrefix = 'item_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Method Information')]);

        if ($model->getId()) {
            $fieldset->addField('caship_id', 'hidden', ['name' => 'caship_id']);
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

        $fieldset->addField('dinamic_price', 'select', [
            'name' => 'dinamic_price',
            'label' => __('Price Source'),
            'title' => __('Price Source'),
            'values' => $this->dynamicPrice->toOptionArray(),
        ]);

        $fieldset->addField('price', 'text', [
            'name' => 'price',
            'label' => __('Price'),
            'title' => __('Price'),
            'value' => 0,
            'style' => 'border-width: 1px;',
            'required' => true,
        ]);

        $fieldset->addField('company_type', 'select', [
            'name' => 'company_type',
            'label' => __('Carrier'),
            'title' => __('Carrier'),
            'values' => $this->shippingCompany->toOptionArray(false),
        ]);

        $fieldset->addField('company_type_all', 'select', [
            'name' => 'company_type_all',
            'label' => __('Module'),
            'title' => __('Module'),
            'values' => $this->shippingCompanyAll->toOptionArray(false),
        ]);

        $fieldset->addField('upsmethod_id', 'select', [
            'name' => 'upsmethod_id',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('ups')
        ]);

        $fieldset->addField('upsmethod_id_all', 'select', [
            'name' => 'upsmethod_id_all',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('ups')
        ]);

        $fieldset->addField('dhlmethod_id', 'select', [
            'name' => 'dhlmethod_id',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('dhl')
        ]);

        $fieldset->addField('dhlmethod_id_all', 'select', [
            'name' => 'dhlmethod_id_all',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('dhl')
        ]);

        $fieldset->addField('fedexmethod_id', 'select', [
            'name' => 'fedexmethod_id',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('fedex')
        ]);

        $fieldset->addField('fedexmethod_id_all', 'select', [
            'name' => 'fedexmethod_id_all',
            'label' => __('Shipping method'),
            'title' => __('Shipping method'),
            'values' => $this->shippingMethods->getMethods('fedex')
        ]);

        $fieldset->addField('added_value_type', 'select', [
            'name' => 'added_value_type',
            'label' => __('Add extra price type'),
            'title' => __('Add extra price type'),
            'values' => $this->addedValueType->toOptionArray(),
        ]);

        $fieldset->addField('added_value_applied_for', 'select', [
            'name' => 'added_value_applied_for',
            'label' => __('Applied Extra Price for'),
            'title' => __('Applied Extra Price for'),
            'options' => array('shipment' => __('Per Shipment'), 'package' => __('Per Package')),
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
            'values' => $this->yesno->toOptionArray()
        ]);

        $fieldset->addField('negotiated_amount_from', 'text', [
            'name' => 'negotiated_amount_from',
            'label' => __('Order amount from for Negotiated rates'),
            'title' => __('Order amount from for Negotiated rates'),
            'value' => 0,
        ]);

        $fieldset->addField('tax', 'select', [
            'name' => 'tax',
            'label' => __('Duty and Tax'),
            'title' => __('Duty and Tax'),
            'values' => $this->yesno->toOptionArray(),
        ]);

        $fieldset->addField('rural', 'select', [
            'name' => 'rural',
            'label' => __('Include Rural Extended'),
            'title' => __('Include Rural Extended'),
            'values' => $this->yesno->toOptionArray(),
        ]);

        $fieldset->addField('time_in_transit', 'select', [
            'name' => 'time_in_transit',
            'label' => __('Time in transit'),
            'title' => __('Time in transit'),
            'values' => $this->yesno->toOptionArray(),
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

        $fieldset->addField('is_country_all', 'select', [
            'name' => 'is_country_all',
            'label' => __('Ship to Applicable Countries'),
            'title' => __('Ship to Applicable Countries'),
            'values' => $this->allSpecificCountries->toOptionArray(),
        ]);

        $fieldset->addField('country_ids', 'multiselect', [
            'name' => 'country_ids',
            'label' => __('Ship to Specific Countries'),
            'title' => __('Ship to Specific Countries'),
            'required' => true,
            'values' => $this->country->toOptionArray(true),
        ]);

        $fieldset->addField('user_group_ids', 'multiselect', [
            'name' => 'user_group_ids',
            'label' => __('User groups'),
            'title' => __('User groups'),
            'values' => $this->customerCollection->toOptionArray(),
        ]);

        $fieldset->addField('is_prod_allow', 'select', [
            'name' => 'is_prod_allow',
            'label' => __('Disable by with Product Attribute'),
            'values' => $this->productAttributes->toOptionArray(),
        ]);

        $fieldset->addField('is_store_all', 'select', [
            'name' => 'is_store_all',
            'label' => __('Apply to Store'),
            'values' => $this->specific->toOptionArray(),
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
            'values' => $this->yesno->toOptionArray(),
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Enabled'),
            'title' => __('Enabled'),
            'values' => $this->yesno->toOptionArray(),
        ]);

        $refFieldUps = $this->_fieldFactory->create(
            ['fieldData' => ['value' => 'ups,upsinfomodus', 'separator' => ','], 'fieldPrefix' => '']
        );
        $refFieldDhl = $this->_fieldFactory->create(
            ['fieldData' => ['value' => 'dhl,dhlinfomodus', 'separator' => ','], 'fieldPrefix' => '']
        );
        $refFieldFedex = $this->_fieldFactory->create(
            ['fieldData' => ['value' => 'fedex,fedexinfomodus', 'separator' => ','], 'fieldPrefix' => '']
        );

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
                "{$htmlIdPrefix}company_type",
                'company_type'
            )->addFieldMap(
                "{$htmlIdPrefix}company_type_all",
                'company_type_all'
            )->addFieldMap(
                "{$htmlIdPrefix}negotiated",
                'negotiated'
            )->addFieldMap(
                "{$htmlIdPrefix}negotiated_amount_from",
                'negotiated_amount_from'
            )->addFieldMap(
                "{$htmlIdPrefix}upsmethod_id",
                'upsmethod_id'
            )->addFieldMap(
                "{$htmlIdPrefix}upsmethod_id_all",
                'upsmethod_id_all'
            )->addFieldMap(
                "{$htmlIdPrefix}dhlmethod_id",
                'dhlmethod_id'
            )->addFieldMap(
                "{$htmlIdPrefix}dhlmethod_id_all",
                'dhlmethod_id_all'
            )->addFieldMap(
                "{$htmlIdPrefix}fedexmethod_id",
                'fedexmethod_id'
            )->addFieldMap(
                "{$htmlIdPrefix}fedexmethod_id_all",
                'fedexmethod_id_all'
            )->addFieldMap(
                "{$htmlIdPrefix}country_ids",
                'country_ids'
            )->addFieldMap(
                "{$htmlIdPrefix}is_country_all",
                'is_country_all'
            )->addFieldMap(
                "{$htmlIdPrefix}tax",
                'tax'
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
                "{$htmlIdPrefix}added_value_applied_for",
                'added_value_applied_for'
            )->addFieldMap(
                "{$htmlIdPrefix}added_value",
                'added_value'
            )->addFieldMap(
                "{$htmlIdPrefix}is_store_all",
                'is_store_all'
            )->addFieldMap(
                "{$htmlIdPrefix}store_id",
                'store_id'
            )->addFieldMap(
                "{$htmlIdPrefix}rural",
                'rural'
            )->addFieldDependence(
                'price',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'store_id',
                'is_store_all',
                '1'
            )->addFieldDependence(
                'company_type',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'company_type_all',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'rural',
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
            )->addFieldDependence(
                'add_day',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'upsmethod_id',
                'company_type',
                'ups'
            )->addFieldDependence(
                'rural',
                'company_type',
                'ups'
            )->addFieldDependence(
                'upsmethod_id_all',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'tax',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'time_in_transit',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'tit_show_format',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'add_day',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'tit_close_hour',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'add_day',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'tit_show_format',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'tit_close_hour',
                'time_in_transit',
                '1'
            )->addFieldDependence(
                'dhlmethod_id',
                'company_type',
                'dhl'
            )->addFieldDependence(
                'dhlmethod_id_all',
                'company_type_all',
                $refFieldDhl
            )->addFieldDependence(
                'fedexmethod_id',
                'company_type',
                'fedex'
            )->addFieldDependence(
                'fedexmethod_id_all',
                'company_type_all',
                $refFieldFedex
            )->addFieldDependence(
                'upsmethod_id',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'upsmethod_id_all',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'dhlmethod_id',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'dhlmethod_id_all',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'fedexmethod_id',
                'dinamic_price',
                '0'
            )->addFieldDependence(
                'fedexmethod_id_all',
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
            )->addFieldDependence(
                'added_value_type',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'added_value_applied_for',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'added_value',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'negotiated_amount_from',
                'negotiated',
                '1'
            )->addFieldDependence(
                'negotiated',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'negotiated',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'negotiated_amount_from',
                'company_type_all',
                $refFieldUps
            )->addFieldDependence(
                'negotiated_amount_from',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'tax',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'add_day',
                'dinamic_price',
                '1'
            )->addFieldDependence(
                'country_ids',
                'is_country_all',
                '1'
            )
        );
        $data = $model->getData();
        if (isset($data['company_type'])) {
            $data['company_type_all'] = $data['company_type'];
        }

        if (isset($data['upsmethod_id'])) {
            $data['upsmethod_id_all'] = $data['upsmethod_id'];
        }

        if (isset($data['dhlmethod_id'])) {
            $data['dhlmethod_id_all'] = $data['dhlmethod_id'];
        }

        if (isset($data['fedexmethod_id'])) {
            $data['fedexmethod_id_all'] = $data['fedexmethod_id'];
        }

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
