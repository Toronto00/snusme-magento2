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

namespace Vconnect\Allinone\Controller\Adminhtml\Postnord;

class Transit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendQuoteSession;

    /**
     * @var \Vconnect\Allinone\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Vconnect\Allinone\Model\Apiclient
     */
    protected $vconnectApiclient;

    /**
     * City constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Vconnect\Allinone\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Vconnect\Allinone\Model\Apiclient $vconnectApiclient
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->dataHelper = $dataHelper;
        $this->vconnectApiclient = $vconnectApiclient;

        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $quote = $this->backendQuoteSession->getQuote();

        $shippingAddress = $quote->getShippingAddress();

        $result = $this->resultJsonFactory->create();

        if(!$this->dataHelper->getStoreConfigFlag('carriers/vconnect_postnord_pickup/active') || !$shippingAddress){
            return $result->setData(['success' => false, 'message' => 'method not active']);
        }

        $apiKey = $this->dataHelper->getStoreConfig('carriers/vconnectpostnord/api_key');
        $postcode = $this->getRequest()->getPost('postcode', $shippingAddress->getPostcode());
        $countryId = $this->getRequest()->getPost('country_id', $shippingAddress->getCountryId());
        $serviceCode = trim($this->getRequest()->getPost('service_code', false));

        $storeCountryId = $this->dataHelper->getStoreConfig('shipping/origin/country_id');
        $storePostcode = $this->dataHelper->getStoreConfig('shipping/origin/postcode');

        if (empty($storeCountryId) || empty($storePostcode) || empty($countryId) || empty($postcode)) {
            $response = array();
            $response['postnord']['error'] = true;
            $response['postnord']['message'] = 'Invalid transittime input data';
        } else {
            $dataParams = array(
                'serviceCode'                 => !empty($serviceCode) ? $serviceCode : 32,
                'serviceGroupCode'            => $storeCountryId,
                'fromAddressPostalCode'       => $storePostcode,
                'fromAddressCountryCode'      => $storeCountryId,
                'toAddressPostalCode'         => $postcode,
                'toAddressCountryCode'        => $countryId,
            );

            $response = $this->vconnectApiclient->getTransitTimeInformation($apiKey, $dataParams);
        }

        return $result->setData($response);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed('Vconnect_Allinone::config');
    }
}
