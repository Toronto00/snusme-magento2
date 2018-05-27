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

class Points extends \Magento\Framework\App\Action\Action
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
        \Vconnect\Allinone\Model\Apiclient $vconnectApiclient
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
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
        $quote = $this->checkoutSession->getQuote();

        $shippingAddress = $quote->getShippingAddress();

        $result = $this->resultJsonFactory->create();

        if(!$this->dataHelper->getStoreConfigFlag('carriers/vconnect_postnord_pickup/active') || !$shippingAddress){
            return $result->setData(['success' => false, 'message' => 'method not active']);
        }

        $postcode = $shippingAddress->getPostcode();
        $countryId = $shippingAddress->getCountryId();
        $street = $shippingAddress->getStreetFull();

        if ($this->getRequest()->getPost('postcode')) {
            $postcode = $this->getRequest()->getPost('postcode');
        }

        if ($this->getRequest()->getPost('country_id')) {
            $countryId = $this->getRequest()->getPost('country_id');
        }

        if ($this->getRequest()->getPost('street')) {
            $street = $this->getRequest()->getPost('street');
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

        if (!empty($postcode) && !empty($countryId) && empty($street)) {
            $street = 'test';
        }

        $apiKey = $this->dataHelper->getStoreConfig('carriers/vconnectpostnord/api_key');

        $response = $this->vconnectApiclient->findPoints($apiKey, $postcode, $countryId, 10, $street);

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
