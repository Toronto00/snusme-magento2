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

namespace Vconnect\Allinone\Model\System\Config\Backend\Shipping;

class License extends \Magento\Framework\App\Config\Value
{
    protected $_configValueFactory;
    protected $_client;
    protected $_dataHelper;
    protected $_request;
    protected $_productMetadata;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Framework\HTTP\ZendClient $client,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_configValueFactory = $configValueFactory;
        $this->_client = $client;
        $this->_request = $request;
        $this->_productMetadata = $productMetadata;
        $this->_objectManager = $objectManager;
        $this->_dataHelper = $dataHelper;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $status = 0;
        $error = '';
        $success = '';

        if (strlen($this->getValue()) == '20') {
            $data = array(
                'license_key'   => $this->getValue(),
                'email'         => $this->_dataHelper->getStoreConfig('trans_email/ident_general/email'),
                'ip'            => $this->_request->getClientIp(),
                'domain'        => $this->_request->getHttpHost(),
                'client'        => 'Magento ' . $this->_productMetadata->getVersion(),
            );

            $url = 'http://api.vconnect.systems/v1/licenses/activate';

            $this->_client->setUri($url);
            $this->_client->setParameterGet($data);

            try {
                $response = $this->_client->request();
                $data = json_decode($response->getBody(), true);
                if ($data) {
                    if (!empty($data['error'])) {
                        if ($data['error'] == '2000' || $data['error'] == '4003' || $data['error'] == '4001') {
                            $status = 1;
                            $success = 'PostNord: Validation success';
                        } else {
                            $status = 0;
                            $error = $data['description'];
                        }
                    } else {
                       $status = 1;
                       $success = 'PostNord: Validation success';
                    }
                } else {
                   $error = 'PostNord: System error';
                }
            } catch (\Exception $e) {
                $error = 'PostNord: System error';
            }
        } else {
            $error = 'PostNord: Invalid license key';
        }

        $configValue = $this->_configValueFactory->create();
        $configValue->load('carriers/vconnectpostnord/license_status', 'path');
        $configValue->setValue($status);
        $configValue->setPath('carriers/vconnectpostnord/license_status');
        $configValue->save();

        $messageManager = $this->_objectManager->get('Magento\Framework\Message\ManagerInterface');

        if ($error) {
            $messageManager->addErrorMessage($error);
        }
        if ($success) {
            $messageManager->addSuccessMessage($success);
        }

        parent::beforeSave();
    }
}