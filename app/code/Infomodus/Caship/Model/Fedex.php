<?php
namespace Infomodus\Caship\Model;

use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;

class Fedex extends \Magento\Fedex\Model\Carrier
{
    public $soapLocation;
    protected $_code = 'fedexlabel';
    private $masterTrackingId = null;

    public $UserID;
    public $Password;
    public $shipperNumber;
    public $meterNumber;

    public $packages;
    public $weightUnits;
    public $dropoff;
    public $packagingType;

    public $unitOfMeasurement;
    public $length;
    public $width;
    public $height;

    public $shipperName;
    public $shipperPhoneNumber;
    public $shipperAddressLine1;
    public $shipperAddressLine2;
    public $shipperCity;
    public $shipperStateProvinceCode;
    public $shipperPostalCode;
    public $shipperCountryCode;
    public $shipperAttentionName;
    public $shipperResidential;
    public $shipperTinType;
    public $shipperTinNumber;

    public $shiptoCompanyName;
    public $shiptoAttentionName;
    public $shiptoPhoneNumber;
    public $shiptoAddressLine1;
    public $shiptoAddressLine2;
    public $shiptoCity;
    public $shiptoStateProvinceCode;
    public $shiptoPostalCode;
    public $shiptoCountryCode;
    public $residentialAddress;

    public $serviceCode;

    public $saturdayDelivery;

    public $trackingNumber;
    public $graphicImage = "PDF";
    public $paperSize = "";

    public $codYesNo;
    public $currencyCode;
    public $codMonetaryValue;
    public $testing;
    /*public $shipmentcharge = 0;*/
    public $qvn = 0;
    public $qvn_code = [];
    public $qvn_email_shipper = '';
    public $qvn_email_shipto = '';
    public $adult;
    public $fedexAccount = 'SENDER';
    public $accountData;
    public $international_invoice = 0;
    public $international_description;
    public $international_comments;
    public $international_invoicenumber;
    public $international_reasonforexport;
    public $international_reasonforexport_desc;
    public $international_termsofsale;
    public $international_purchaseordernumber;
    public $international_products;
    public $international_invoicedate;
    public $international_purpose_of_shipment;

    public $isElectronicTradeDocuments = 1;

    /* Pickup */
    public $RatePickupIndicator;
    public $CloseTime;
    public $ReadyTime;
    public $PickupDateYear;
    public $PickupDateMonth;
    public $PickupDateDay;
    public $AlternateAddressIndicator;
    public $ServiceCode;
    public $Quantity;
    public $DestinationCountryCode;
    public $ContainerCode;
    public $Weight;
    public $OverweightIndicator;
    public $PaymentMethod;
    public $SpecialInstruction;
    public $ReferenceNumber;
    public $Notification;
    public $ConfirmationEmailAddress;
    public $UndeliverableEmailAddress;
    public $room;
    public $floor;
    public $urbanization;
    public $residential;
    public $pickup_point;
    /* END Pickup */

    public $storeId = null;

    public $_conf;
    public $insured_automaticaly;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Infomodus\Caship\Helper\Config $config,
        array $data = []
    )
    {
        $this->_storeManager = $storeManager;
        $this->_conf = $config;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $storeManager,
            $configReader,
            $productCollectionFactory,
            $data
        );
        $wsdlBasePath = $configReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Infomodus_Caship') . '/wsdl/FedEx/';
        $this->_rateServiceWsdl = $wsdlBasePath . 'RateService_v20.wsdl';
    }

    /**
     * @param string $type
     * @return array
     */
    public function getShipPrice()
    {
        $totalWeight = 0;
        $valueToDb = [];
        $debugData = ['result' => []];
        $debugData['result']['code'] = '';
        $debugData['result']['error'] = '';
        foreach ($this->packages as $k => $package) {
            $totalWeight += round($package['weight'] + $package['packweight'], 2);
        }
        $shipper = [
            'Contact' => [
                'PersonName' => $this->shipperAttentionName,
                'CompanyName' => $this->shipperName,
                'PhoneNumber' => $this->shipperPhoneNumber
            ],
            'Address' => [
                'StreetLines' => [
                    $this->shipperAddressLine1
                ],
                'City' => $this->shipperCity,
                'StateOrProvinceCode' => $this->shipperStateProvinceCode,
                'PostalCode' => $this->shipperPostalCode,
                'CountryCode' => $this->shipperCountryCode
            ]
        ];
        if ($this->shipperTinType != "") {
            $shipper['Tins'] = [
                'TinType' => $this->shipperTinType,
                'Number' => $this->shipperTinNumber
            ];
        }

        $recipient = [
            'Contact' => [
                'PersonName' => $this->shiptoAttentionName,
                'CompanyName' => $this->shiptoCompanyName,
                'PhoneNumber' => $this->shiptoPhoneNumber
            ],
            'Address' => [
                'StreetLines' => [
                    $this->shiptoAddressLine1,
                    $this->shiptoAddressLine2
                ],
                'City' => $this->shiptoCity,
                'StateOrProvinceCode' => $this->shiptoStateProvinceCode,
                'PostalCode' => $this->shiptoPostalCode,
                'CountryCode' => $this->shiptoCountryCode,
                'Residential' => (bool)$this->residentialAddress
            ],
        ];

        $requestClient = [
            'ReturnTransitAndCommit' => true,
            'RequestedShipment' => [
                'ShipTimestamp' => date('c', time()),
                'DropoffType' => $this->dropoff,
                'PackagingType' => $this->packagingType,
                'ServiceType' => $this->serviceCode,
                'Shipper' => $shipper,
                'Recipient' => $recipient,
                'PackageCount' => count($this->packages),
            ]
        ];



        if ($this->fedexAccount == 'SENDER') {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'SENDER',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->shipperNumber
                ]]
            ];
        } elseif ($this->fedexAccount == 'RECIPIENT') {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'RECIPIENT',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->shipperNumber
                ]]
            ];
        } else {
            $requestClient['RequestedShipment']['ShippingChargesPayment'] = [
                'PaymentType' => 'THIRD_PARTY',
                'Payor' => ['ResponsibleParty' => [
                    'AccountNumber' => $this->accountData->getAccountnumber()
                ]]
            ];
        }

        foreach ($this->packages as $k => $package) {
            $packagesArray = [
                'SequenceNumber' => ($k + 1),
                'GroupNumber' => 1/*($k + 1)*/,
                'GroupPackageCount' => 1/*($k + 1)*/,
                'Weight' => [
                    'Units' => $this->weightUnits,
                    'Value' => round($package['weight'] + $package['packweight'], 2)
                ],
                'SpecialServicesRequested' => [
                    [
                        'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                        'SignatureOptionDetail' => ['OptionType' => $this->adult]
                    ],
                ],
            ];
            if ($this->shiptoCountryCode == 'IN') {
                $packagesArray['CustomerReferences'][] = [
                    'CustomerReferenceType' => 'INVOICE_NUMBER',
                    'Value' => $this->international_invoicenumber . 'Dated : ' . $this->international_invoicedate
                ];
            }

            if ($this->insured_automaticaly == 1) {
                $packagesArray['InsuredValue'] = [
                    'Currency' => $this->_conf->getStoreConfig('currency/options/base', $this->storeId),
                    'Amount' => round($this->codMonetaryValue, 2)
                ];
                $this->insured_automaticaly = 0;
            }

            if ($this->masterTrackingId === null) {
                $requestClient['RequestedShipment']['TotalWeight'] = [
                    'Units' => $this->weightUnits, 'Value' => $totalWeight
                ];
            }
            if (isset($package['cod']) && $package['cod'] == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] = 'COD';
                $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail'] = [
                    'CodCollectionAmount' => [
                        'Amount' => $this->codMonetaryValue,
                        'Currency' => $this->_conf->getStoreConfig('currency/options/base', $this->storeId)
                    ],
                    'CollectionType' => 'ANY'
                ];
                if ($this->shiptoCountryCode == 'IN') {
                    $requestClient['RequestedShipment']['SpecialServicesRequested']['CodDetail']['CollectionType'] =
                        'CASH';
                }
            }

            if ($this->saturdayDelivery == 1) {
                $requestClient['RequestedShipment']['SpecialServicesRequested']['SpecialServiceTypes'][] =
                    'SATURDAY_DELIVERY';
            }

            $this->_conf->log(json_encode($package));

            if (!empty($package['length']) || !empty($package['width']) || !empty($package['height'])) {
                $dimensions = [];
                $dimensions['Length'] = round($package['length'], 0);
                $dimensions['Width'] = round($package['width'], 0);
                $dimensions['Height'] = round($package['height'], 0);
                $dimensions['Units'] = $this->unitOfMeasurement;
                $packagesArray['Dimensions'] = $dimensions;
            }

            $requestClient['RequestedShipment']['RequestedPackageLineItems'][] = $packagesArray;

            // for international shipping
            if ($this->shipperCountryCode != $this->shiptoCountryCode) {
                $requestClient['RequestedShipment']['EdtRequestType'] = 'ALL';
                if ($this->_conf->getStoreConfig('fedexlabel/ratepayment/dytytaxinternational', $this->storeId) === 'shipper') {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'SENDER',
                        'Payor' => ['ResponsibleParty' => [
                            'AccountNumber' => $this->shipperNumber
                        ]]
                    ];
                } else {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['DutiesPayment'] = [
                        'PaymentType' => 'RECIPIENT',
                        /*'Payor' => array('ResponsibleParty' => array(
                            'AccountNumber' => $this->shipperNumber
                        ))*/
                    ];
                }

                $requestClient = $this->getCommodities($requestClient);
            }
        }

        $requestClient['ReturnTransitAndCommit'] = true;
        $request = $this->_getAuthDetails('crs', 20) + $requestClient;
        /*print_r($request); exit;*/
        $client = $this->_createRateSoapClient();
        $response = $client->getRates($request);
        //print_r($client->__getLastRequest());
        //print_r($client->__getLastResponse());
        $this->_conf->log($client->__getLastRequest());
        $this->_conf->log($client->__getLastResponse());
        /*$this->_conf->log($this->xml2array($response));*/
        /*print_r($response);*/
        if (is_soap_fault($response)) {
            if (isset($response->detail->desc)) {
                $debugData['result']['code'] .= $response->faultcode . '; ';
                $debugData['result']['error'] .= $response->detail->desc . '; ';
            }

            return array('error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                'request' => $request/*, 'response' => $this->xml2array($response)*/]);
        } else {
            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'
                && isset($response->RateReplyDetails)
            ) {
                $response = $response->RateReplyDetails;

                $responses = $response;
                if(!is_array($responses)){
                    $responses2 = [];
                    $responses2[0] = $responses;
                    $responses = $responses2;
                }

                foreach($responses AS $response) {
                    if (array_key_exists('DeliveryTimestamp', $response)) {
                        $time = $response->DeliveryTimestamp;
                    } elseif (array_key_exists('TransitTime', $response)) {
                        $time = $response->TransitTime;
                    } else {
                        $time = '';
                    }

                    if (isset($response->RatedShipmentDetails) && is_array($response->RatedShipmentDetails)) {
                        foreach ($response->RatedShipmentDetails as $replyPrice) {
                            $valueToDb[$replyPrice->ServiceType] = ['time' => $time,
                                'currency' => $replyPrice->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Currency,
                                'price' => number_format($replyPrice->ShipmentRateDetail
                                    ->TotalNetChargeWithDutiesAndTaxes->Amount, 2, ".", ",")];
                        }
                    } elseif (isset($response->RatedShipmentDetails) && !is_array($response->RatedShipmentDetails)) {
                        $valueToDb[$response->ServiceType] = ['time' => $time,
                            'currency' => $response->RatedShipmentDetails->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Currency,
                            'price' =>
                                number_format($response->RatedShipmentDetails->ShipmentRateDetail->TotalNetChargeWithDutiesAndTaxes->Amount,
                                    2, ".", ",")];
                    }
                }
            } else {
                if (is_array($response->Notifications)) {
                    foreach ($response->Notifications as $notification) {
                        $debugData['result']['code'] .= $notification->Code . '; ';
                        $debugData['result']['error'] .= $notification->Message . '; ';
                    }
                } else {
                    $debugData['result']['code'] = $response->Notifications->Code . ' ';
                    $debugData['result']['error'] = $response->Notifications->Message . ' ';
                }

                return ['error' => ['code' => $debugData['result']['code'], 'desc' => $debugData['result']['error'],
                    'request' => $request/*, 'response' => $this->xml2array($response)*/]];
            }
        }

        return $valueToDb;
    }

    public function xml2array($xmlObject)
    {
        $outString = json_encode($xmlObject);
        unset($xmlObject);
        return json_decode($outString, true);
    }

    protected function _getAuthDetails($type = 'ship', $major = 17)
    {
        return array(
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $this->UserID,
                    'Password' => $this->Password
                ]
            ],
            'ClientDetail' => [
                'AccountNumber' => $this->shipperNumber,
                'MeterNumber' => $this->meterNumber
            ],
            'TransactionDetail' => [
                'CustomerTransactionId' => '*** Express Domestic Shipping Request v10 using PHP ***'
            ],
            'Version' => [
                'ServiceId' => $type,
                'Major' => $major,
                'Intermediate' => '0',
                'Minor' => '0'
            ]
        );
    }

    protected function _createRateSoapClient()
    {
        return $this->_createSoapClient($this->_rateServiceWsdl, 1);
    }

    protected function _createSoapClient($wsdl, $trace = false)
    {
        $client = new \SoapClient($wsdl, ['trace' => $trace, 'exceptions' => 0, 'cache_wsdl' => WSDL_CACHE_NONE]);
        $this->soapLocation = $this->testing == 1
            ? 'https://wsbeta.fedex.com:443/web-services'
            : 'https://ws.fedex.com:443/web-services';
        $client->__setLocation($this->soapLocation);

        return $client;
    }

    private function getCommodities($requestClient)
    {
        $sumPrice = 0;
        $currency = '';
        if (!empty($this->international_products)) {
            $i = 0;
            foreach ($this->international_products as $kp => $products) {
                $requestClient['RequestedShipment']['CustomsClearanceDetail']['Commodities'][$i] = [
                    'Weight' => [
                        'Units' => $this->weightUnits,
                        'Value' => round($products['weight'], 2)
                    ],
                    'NumberOfPieces' => ($kp + 1),
                    'CountryOfManufacture' => $products['country_code'],
                    'Description' => $products['description'],
                    'Quantity' => ceil($products['qty']),
                    'QuantityUnits' => 'pcs',
                    'UnitPrice' => [
                        'Currency' => $this->getTrueCurrency($this->_conf->getStoreConfig('currency/options/base', $this->storeId)),
                        'Amount' => $products['price']
                    ],
                    'CustomsValue' => [
                        'Currency' => $this->getTrueCurrency($this->_conf->getStoreConfig('currency/options/base', $this->storeId)),
                        'Amount' => $products['price']
                    ],
                ];
                if (!empty($products['harmonized'])) {
                    $requestClient['RequestedShipment']['CustomsClearanceDetail']['Commodities'][$i]['HarmonizedCode']
                        = $products['harmonized'];
                }

                $sumPrice += $products['price'];
                $currency = $this->_conf->getStoreConfig('currency/options/base', $this->storeId);

                $i++;
            }
        }

        $requestClient['RequestedShipment']['CustomsClearanceDetail']['CustomsValue'] = array(
            'Currency' => $this->getTrueCurrency($currency),
            'Amount' => $sumPrice,
        );
        $requestClient['RequestedShipment']['CustomsClearanceDetail']['DocumentContent'] = 'NON_DOCUMENTS';

        return $requestClient;
    }

    private function getTrueCurrency($currency)
    {
        $currencies = array('CHF' => 'SFR');
        if (isset($currencies[$currency])) {
            return $currencies[$currency];
        }

        return $currency;
    }
}
