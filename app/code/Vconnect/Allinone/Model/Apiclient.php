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

namespace Vconnect\Allinone\Model;

class Apiclient
{
    const POSTNORD_API_URL = "https://api2.postnord.com/rest/";

    protected $_client;
    protected $_dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Allinone helper constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\HTTP\ZendClient $client,
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Vconnect\Allinone\Helper\Data $dataHelper,
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\HTTP\ZendClient $client,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_client = $client;
        $this->_checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * 
     * @param string $apiKey
     * @param int $postCode
     * @param string $countryCode
     * @param int $limit
     * @param string $street
     * @param string $locale Allowed values are en, sv, no, da and fi
     * @return array
     */
    public function findPoints($apiKey, $postCode = 2100, $countryCode='DK', $limit = 10, $street = null, $locale='en') 
    {
        $paramsAllinonePoints = array(
            $countryCode,
            $postCode,
            $street,
        );

        if ($this->_checkoutSession->hasAllinonePoints()) {
            $sessionAioPoitns = $this->_checkoutSession->getAllinonePoints();

            if (!empty($sessionAioPoitns['response']) && !empty($sessionAioPoitns['params_string']) && $sessionAioPoitns['params_string'] == implode(',', $paramsAllinonePoints) && !empty($sessionAioPoitns['date_created']) && (time() - $sessionAioPoitns['date_created']) < 240) {
                return $sessionAioPoitns['response'];
            }
        }

        $allowed_agreement_countries = array('SE','DK','FI');

        $start = microtime();

        $params = array(
            'apikey'                 => $apiKey,
            'countryCode'            => strtoupper($countryCode),
            'agreementCountry'       => (in_array(strtoupper($this->_dataHelper->getStoreConfig('shipping/origin/country_id')), $allowed_agreement_countries) ? strtoupper($this->_dataHelper->getStoreConfig('shipping/origin/country_id')) : ''),
            'postalCode'             => str_replace(' ', '', $postCode),
            'streetName'             => $street,
            'numberOfServicePoints'  => $limit,
            'locale'                 => $locale
        );

        $url = self::POSTNORD_API_URL . 'businesslocation/v1/servicepoint/findNearestByAddress.json';

        $this->_client->setUri($url);
        $this->_client->setParameterGet($params);

        $result['postnord'] = array();
        try {
            $response = $this->_client->request();
            if ($response->getStatus() == 200) {
                $res = json_decode($response->getBody(), true);
                $result['postnord'] = $res;
            } else {
                $result['postnord']['error'] = true;
                $result['postnord']['message'] = "API returned error with code {$response->getStatus()}";
            }
        } catch (Exception $ex) {
             $result['postnord']['error'] = true;
             $result['postnord']['message'] = $ex->getMessage();
        }

        $result['time'] = (float)microtime()- (float)$start;

        $sessionAioPoitns = array(
            'params_string' => implode(',', $paramsAllinonePoints),
            'date_created' => time(),
            'response' => $result,
        );
        $this->_checkoutSession->setAllinonePoints($sessionAioPoitns);

        return $result;
    }

    /**
     * 
     * @param string $apiKey
     * @param array $dataParams
     * @return array
     */
    public function getTransitTimeInformation($apiKey, $dataParams = array()) 
    {
        $start = microtime();

        $params = array(
            'apikey'                     => $apiKey,
            'serviceCode'                => $dataParams['serviceCode'],
            'serviceGroupCode'           => strtoupper($dataParams['serviceGroupCode']),
            'fromAddressPostalCode'      => $dataParams['fromAddressPostalCode'],
            'fromAddressCountryCode'     => strtoupper($dataParams['fromAddressCountryCode']),
            'toAddressPostalCode'        => str_replace(' ', '', $dataParams['toAddressPostalCode']),
            'toAddressCountryCode'       => strtoupper($dataParams['toAddressCountryCode'])
        );

        $url = self::POSTNORD_API_URL . 'transport/v1/transittime/getTransitTimeInformation.json';

        $this->_client->setUri($url);
        $this->_client->setParameterGet($params);

        $result['postnord'] = array();
        try {
            $response = $this->_client->request();
            if ($response->getStatus() == 200) {
                $res = json_decode($response->getBody(), true);
                $result['postnord'] = $res;
                
            } else {
                $result['postnord']['error'] = true;
                $result['postnord']['message'] = "API returned error with code {$response->getStatus()}";
            }
        } catch (Exception $ex) {
             $result['postnord']['error'] = true;
             $result['postnord']['message'] = $ex->getMessage();
        }

        $result['time'] = (float)microtime()- (float)$start;
        return $result;
    }
}
