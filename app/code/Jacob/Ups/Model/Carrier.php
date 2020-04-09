<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Jacob\Ups\Model;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Simplexml\Element;
use Magento\Ups\Helper\Config;
use Magento\Framework\Xml\Security;

/**
 * UPS shipping implementation
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends \Magento\Ups\Model\Carrier implements CarrierInterface
{
    public $_filesystem;
    public $_directoryList;
    private $productRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Config $configHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Config $configHelper,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_filesystem = $filesystem;
        $this->_directoryList = $directoryList;
        $this->productRepository = $productRepository;
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
            $localeFormat,
            $configHelper,
            $data
        );
    }

    /**
     * Form XML for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == \Zend_Measure_Weight::POUND ? 'LBS' : 'KGS';
        $dimensionsUnits = $packageParams->getDimensionUnits() == \Zend_Measure_Length::INCH ? 'IN' : 'CM';

        $itemsDesc = [];
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        $xmlRequest = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>']
        );
        $requestPart = $xmlRequest->addChild('Request');
        $requestPart->addChild('RequestAction', 'ShipConfirm');
        $requestPart->addChild('RequestOption', 'nonvalidate');

        $shipmentPart = $xmlRequest->addChild('Shipment');
        if ($request->getIsReturn()) {
            $returnPart = $shipmentPart->addChild('ReturnService');
            // UPS Print Return Label
            $returnPart->addChild('Code', '9');
        }
        $shipmentPart->addChild('Description', $this->getConfigData('description'));
        //empirical

        $shipperPart = $shipmentPart->addChild('Shipper');
        if ($request->getIsReturn()) {
            $shipperPart->addChild('Name', $request->getRecipientContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getRecipientContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
            $addressPart->addChild('City', $request->getRecipientAddressCity());
            $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressStateOrProvinceCode());
            }
        } else {
            $shipperPart->addChild('Name', $request->getShipperContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getShipperContactPhoneNumber());
            $shipperPart->addChild('TaxIdentificationNumber',
                $this->_scopeConfig->getValue(
                    \Magento\Store\Model\Information::XML_PATH_STORE_INFO_VAT_NUMBER,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                )
            );

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
        }

        foreach (['SoldTo', 'ShipTo'] as $target) {
            $shipToPart = $shipmentPart->addChild($target);
            $shipToPart->addChild('AttentionName', $request->getRecipientContactPersonName());
            $shipToPart->addChild(
                'CompanyName',
                $request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : 'N/A'
            );
            $shipToPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

            $addressPart = $shipToPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet1());
            $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
            $addressPart->addChild('City', $request->getRecipientAddressCity());
            $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressRegionCode());
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $addressPart->addChild('ResidentialAddress');
            }
        }

        if ($request->getIsReturn()) {
            $shipFromPart = $shipmentPart->addChild('ShipFrom');
            $shipFromPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipFromPart->addChild(
                'CompanyName',
                $request->getShipperContactCompanyName() ? $request
                    ->getShipperContactCompanyName() : $request
                    ->getShipperContactPersonName()
            );
            $shipFromAddress = $shipFromPart->addChild('Address');
            $shipFromAddress->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $shipFromAddress->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $shipFromAddress->addChild('City', $request->getShipperAddressCity());
            $shipFromAddress->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $shipFromAddress->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $shipFromAddress->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }

            $addressPart = $shipToPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $addressPart->addChild('ResidentialAddress');
            }
        }

        $servicePart = $shipmentPart->addChild('Service');
        $servicePart->addChild('Code', $request->getShippingMethod());
        $packagePart = $shipmentPart->addChild('Package');
        $packagePart->addChild('Description', $this->getConfigData('description'));
        //empirical
        $packagePart->addChild('PackagingType')->addChild('Code', $request->getPackagingType());
        $packageWeight = $packagePart->addChild('PackageWeight');
        $packageWeight->addChild('Weight', $request->getPackageWeight());
        $packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $weightUnits);

        // set dimensions
        if ($length || $width || $height) {
            $packageDimensions = $packagePart->addChild('Dimensions');
            $packageDimensions->addChild('UnitOfMeasurement')->addChild('Code', $dimensionsUnits);
            $packageDimensions->addChild('Length', $length);
            $packageDimensions->addChild('Width', $width);
            $packageDimensions->addChild('Height', $height);
        }

        // ups support reference number only for domestic service
        if ($this->_isUSCountry(
                $request->getRecipientAddressCountryCode()
            ) && $this->_isUSCountry(
                $request->getShipperAddressCountryCode()
            )
        ) {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . $request->getPackageId();
            } else {
                $referenceData = 'Order #' .
                    $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $referencePart = $packagePart->addChild('ReferenceNumber');
            $referencePart->addChild('Code', '02');
            $referencePart->addChild('Value', $referenceData);
        }

        $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
        $serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
        $internationalForms = $serviceOptionsNode->addChild('InternationalForms');

        $invoiceDate = date('Ymd', time());

        foreach ($request->getOrderShipment()->getOrder()->getInvoiceCollection() as $invoice) {
            $createdAt = $invoice->getCreatedAt();

            if ($timestamp = strtotime($createdAt)) {
                $invoiceDate = date('Ymd', $timestamp);
            }
        }

        $shippingAmount = (float)$request->getOrderShipment()->getOrder()->getShippingAmount();
        $discountAmount = abs((float)$request->getOrderShipment()->getOrder()->getDiscountAmount());

        $internationalForms->addChild('FormType', '01');
        $internationalForms->addChild('InvoiceNumber', $request->getOrderShipment()->getOrder()->getIncrementId());
        $internationalForms->addChild('InvoiceDate', $invoiceDate);
        $internationalForms->addChild('PurchaseOrderNumber', $request->getOrderShipment()->getOrder()->getIncrementId());
        $internationalForms->addChild('ReasonForExport', 'For personal use');
        $internationalForms->addChild('CurrencyCode', $request->getBaseCurrencyCode());
        $internationalForms->addChild('DeclarationStatement', "I hereby certify that the information on this invoice is true and correct and the contents and value of this shipment is as stated above. The exporter of the products covered by this document declares that except where otherwise clearly indicated these products are of EEA preferential origin.");

        $totalItemCost = 0;

        if ($request->getRecipientAddressCountryCode() == 'US') {
            $internationalForms->addChild('TermsOfShipment', 'DDP');
        }

        foreach ($request->getPackageItems() as $item) {
            $product = $internationalForms->addChild('Product');
            $totalItemCost += (float)$item['price'];
            $name = strlen($item['name']) > 35 ? substr($item['name'], 0, 35) : $item['name'];

            $productObject = $this->productRepository->getById($item['product_id']);
            $nicotineWeight = $productObject->getNicotineWeight();

            $product->addChild('Description', $name);
            $product->addChild('Description', "\r\nContent: $nicotineWeight g");
            //$product->addChild('CommodityCode', '24039910');
            //$product->addChild('PartNumber', $item['product_id']);
            $product->addChild('OriginCountryCode', $request->getShipperAddressCountryCode());
            $productUnit = $product->addChild('Unit');

            $productUnit->addChild('Number', $item['qty']);
            $productUnit->addChild('Value', $item['customs_value']);
            $productUnit->addChild('UnitOfMeasurement')
                ->addChild('Code', 'PCS');

            $productWeight = $product->addChild('ProductWeight');
            $productWeight->addChild('UnitOfMeasurement')->addChild('Code', 'KGS');
            $productWeight->addChild('Weight', round($item['weight'], 1));
        }

        $totalCalculated    = $totalItemCost + $shippingAmount - $discountAmount;
        $diff               = (float)$request->getOrderShipment()->getOrder()->getGrandTotal() - $totalCalculated;

        $internationalForms->addChild('FreightCharges')
            ->addChild('MonetaryValue', $shippingAmount);
        $internationalForms->addChild('Discount')
            ->addChild('MonetaryValue', $discountAmount);

        if ($diff > 0) {
            $otherCharges = $internationalForms->addChild('OtherCharges');

            $otherCharges->addChild('MonetaryValue', round($diff, 2));
            $otherCharges->addChild('Description', 'Other');
        }

        $packageServiceOptions = $packagePart->addChild('PackageServiceOptions');
        $packageServiceOptions->addChild('DeclaredValue')->addChild('MonetaryValue', 0);

        if ($deliveryConfirmation && $this->_getDeliveryConfirmationLevel($request->getRecipientAddressCountryCode()) == self::DELIVERY_CONFIRMATION_PACKAGE) {
            $packageServiceOptions
                ->addChild('DeliveryConfirmation')
                ->addChild('DCISType', $packageParams->getDeliveryConfirmation());
        }


        $shipmentPart->addChild(
            'PaymentInformation'
        )->addChild(
            'Prepaid'
        )->addChild(
            'BillShipper'
        )->addChild(
            'AccountNumber',
            $this->getConfigData('shipper_number')
        );

        if ($request->getPackagingType() != $this->configHelper->getCode(
                'container',
                'ULE'
            ) &&
            $request->getShipperAddressCountryCode() == self::USA_COUNTRY_ID &&
            ($request->getRecipientAddressCountryCode() == 'CA' ||
                $request->getRecipientAddressCountryCode() == 'PR')
        ) {
            $invoiceLineTotalPart = $shipmentPart->addChild('InvoiceLineTotal');
            $invoiceLineTotalPart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $invoiceLineTotalPart->addChild('MonetaryValue', ceil($packageParams->getCustomsValue()));
        }

        $labelPart = $xmlRequest->addChild('LabelSpecification');
        $labelPart->addChild('LabelPrintMethod')->addChild('Code', 'GIF');
        $labelPart->addChild('LabelImageFormat')->addChild('Code', 'GIF');

        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest . $xmlRequest->asXml();

        return $xmlRequest;
    }

    /**
     * Send and process shipment accept request
     *
     * @param Element $shipmentConfirmResponse
     * @return \Magento\Framework\DataObject
     */
    protected function _sendShipmentAcceptRequest(Element $shipmentConfirmResponse)
    {
        $xmlRequest = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" ?><ShipmentAcceptRequest/>']
        );
        $request = $xmlRequest->addChild('Request');
        $request->addChild('RequestAction', 'ShipAccept');
        $xmlRequest->addChild('ShipmentDigest', $shipmentConfirmResponse->ShipmentDigest);
        $debugData = ['request' => $xmlRequest->asXML()];

        try {
            $ch = curl_init($this->getShipAcceptUrl());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_xmlAccessRequest . $xmlRequest->asXML());
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (bool)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec($ch);

            $debugData['result'] = $xmlResponse;
            $this->_setCachedQuotes($xmlRequest, $xmlResponse);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $xmlResponse = '';
        }

        try {
            $response = $this->_xmlElFactory->create(['data' => $xmlResponse]);
        } catch (\Exception $e) {
            $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
        }

        $result = new \Magento\Framework\DataObject();
        if (isset($response->Error)) {
            $result->setErrors((string)$response->Error->ErrorDescription);
        } else {
            $invoice                = (string)$response->ShipmentResults->Form->Image->GraphicImage;
            $shippingLabelContent   = (string)$response->ShipmentResults->PackageResults->LabelImage->GraphicImage;
            $trackingNumber         = (string)$response->ShipmentResults->PackageResults->TrackingNumber;

            $result->setShippingLabelContent(base64_decode($shippingLabelContent));
            $result->setTrackingNumber($trackingNumber);

            if ($invoice) {
                $writer = $this->_filesystem->getDirectoryWrite('upload');
                $file   = $writer->openFile("{$trackingNumber}.pdf", 'w');

                try {
                    $file->lock();
                    try {
                        $file->write(base64_decode($invoice));
                    } finally {
                        $file->unlock();
                    }
                } finally {
                    $file->close();
                }

            }
        }

        $this->_debug($debugData);

        return $result;
    }
}
