<?php
/* 
 * The MIT License
 *
 * Copyright 2016 vConnect.dk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @category Magento
 * @package Vconnect_AllInOne
 * @author vConnect
 * @email kontakt@vconnect.dk
 * @class Vconnect_AllInOne_Model_System_Config_Backend_Shipping_License
 */

namespace Vconnect\Allinone\Controller\Postnord;

class getPostnordHtml extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Vconnect\Allinone\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Vconnect\Allinone\Model\Apiclient
     */
    protected $vconnectApiclient;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * City constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Vconnect\Allinone\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Vconnect\Allinone\Model\Apiclient $vconnectApiclient,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->dataHelper = $dataHelper;
        $this->vconnectApiclient = $vconnectApiclient;
        $this->layout = $layout;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $_block = $this->layout->createBlock(
            'Vconnect\Allinone\Block\Shipping\Dialog', 'allinone_dialog'
        );

        $blockTabHeaders = $this->layout->createBlock(
            'Magento\Framework\View\Element\Template', 'tab_headers'
        );
        $_block->setChild('tab_headers', $blockTabHeaders);

        $blockBodyScripts = $this->layout->createBlock(
            'Magento\Framework\View\Element\Template', 'body_scripts'
        );
        $_block->setChild('body_scripts', $blockBodyScripts);

        $shipping_address = $_block->getBlockQuote()->getShippingAddress();

        $countryId = '';
        $postcode = '';
        $street = '';

        if (!empty($this->getRequest()->getPost('countryId'))) {
            $countryId = $this->getRequest()->getPost('countryId');
        } elseif ($shipping_address->getCountryId()) {
            $countryId = $shipping_address->getCountryId();
        }

        if (!empty($this->getRequest()->getPost('postcode'))) {
            $postcode = $this->getRequest()->getPost('postcode');
        } elseif ($shipping_address->getPostcode()) {
            $postcode = $shipping_address->getPostcode();
        }

        if (!empty($this->getRequest()->getPost('street'))) {
            $street = $this->getRequest()->getPost('street');
        }

        if (empty($countryId)) {
            $countryId = $this->dataHelper->getStoreConfig('general/country/default');
        }

        if (!empty($countryId) && empty($postcode)) {
            if ($countryId == 'SE') {
                $postcode = '10044';
            } else if ($countryId == 'FI') {
                $postcode = '00100';
            } else if ($countryId == 'NO') {
                $postcode = '0107';
            } else if ($countryId == 'DK') {
                $postcode = '1000';
            }
        }

        $_block->setData('shippingCountryId', $countryId);
        $_block->setData('shippingPostcode', $postcode);
        $_block->setData('shippingStreet', $street);

        $this->shippingMethodsUiCollect($_block);

        $this->getResponse()->setBody($_block->toHtml());
    }

    public function shippingMethodsUiCollect($block) {
        $rates = $block->getRates();
        foreach ($rates as $data) {
            if(!isset($data['vc_method_data']) || !$data['vc_method_data']){
                continue;
            }

            $_data = json_decode($data['vc_method_data'],true);
            $countryCode    = strtolower($_data['country']);
            $methodCode     = $_data['method'];
            $templatePath   = $_data['template'];

            $data['base_amount'] = $data['price'];
            $data['price_formated'] = $this->dataHelper->getPriceFormated($block->getBlockQuote(), $data['base_amount'], true);
            $data['price_formated_withoutcontainer'] = $this->dataHelper->getPriceFormated($block->getBlockQuote(), $data['base_amount'], true, false);
            $data['shippingCountryId'] = $block->getData('shippingCountryId');
            $data['shippingPostcode'] = $block->getData('shippingPostcode');
            $data['shippingStreet'] = $block->getData('shippingStreet');

            foreach (array('tabHeader','tabScripts') as $template){
                $b = $this->layout->createBlock(
                    'Magento\Framework\View\Element\Template', $templatePath.'_'.$template
                );
                $b->setData('allinone_data', $data);
                $b->setData('allinone_config', $_data);
                $b->setData('module_name', 'Vconnect_Allinone');
                $b->setDataHelper($this->dataHelper);
                $b->setBlockQuote($block->getBlockQuote());

                $templateTypePath = 'embedded';

                $b->setTemplate("vconnect/$templateTypePath/$templatePath/$template.phtml");
                $templateFile = $b->getTemplateFile();
                if (!file_exists($templateFile)) {
                    continue;
                }
                $action = sprintf('add%s',  ucfirst($template));
                if(is_callable(array($block, $action))){
                    $block->$action($b);
                }
            }
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed('Vconnect_Allinone::config');
    }
}
