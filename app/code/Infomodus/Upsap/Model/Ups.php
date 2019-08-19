<?php

namespace Infomodus\Upsap\Model;
class Ups
{
    public $_handy;
    public $AccessLicenseNumber;
    public $UserID;
    public $Password;
    public $shipperNumber;

    public $packages;
    public $weightUnits;
    public $packageWeight;

    public $unitOfMeasurement;
    public $length;
    public $width;
    public $height;

    public $shipperCity;
    public $shipperStateProvinceCode;
    public $shipperPostalCode;
    public $shipperCountryCode;

    public $shiptoStateProvinceCode;
    public $shiptoPostalCode;
    public $shiptoCountryCode;
    public $shiptoCity;

    public $shipfromCity;
    public $shipfromStateProvinceCode;
    public $shipfromPostalCode;
    public $shipfromCountryCode;

    public $testing;
    public $dimentionUnitKoef=1;

    public $adult = 0;

    public $shipmentIndicationType = '01';
    public $storeId = NULL;

    public $negotiated_rates = 0;
    public $rates_tax = 0;
    public $residentialAddress = 0;
    public $invoiceLineTotal = 0;
    public $currency = "";
    public $insured;
    public $pickupDate;

    function getShipRate($nr = 0)
    {
        $this->customerContext = $this->shipperNumber;
        $data = "<?xml version=\"1.0\" ?><AccessRequest xml:lang='en-US'><AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber><UserId>" . $this->UserID . "</UserId><Password>" . $this->Password . "</Password></AccessRequest>
<?xml version=\"1.0\"?>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>Shop</RequestOption>
  </Request>
  <PickupType>
          <Code>03</Code>
          <Description>Customer Counter</Description>
  </PickupType>
  <Shipment>
  <TaxInformationIndicator/>";
        if ($nr == 1) {
            $data .= "
   <RateInformation>
      <NegotiatedRatesIndicator/>
    </RateInformation>";
        }

        $data .= "<Shipper>";
        $data .= "<ShipperNumber>" . $this->shipperNumber . "</ShipperNumber>
      <Address>
    	<City>" . $this->shipperCity . "</City>
    	<StateProvinceCode>" . $this->shipperStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipperPostalCode . "</PostalCode>
    	<CountryCode>" . $this->shipperCountryCode . "</CountryCode>
     </Address>
    </Shipper>
	<ShipTo>
      <Address>
        <StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
        <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
        <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>";
        if($this->residentialAddress == 1){
            $data .= "<ResidentialAddressIndicator/>";
        }
        $data .= "</Address>
    </ShipTo>
    <ShipFrom>
      <Address>
    	<StateProvinceCode>" . $this->shipfromStateProvinceCode . "</StateProvinceCode>
    	<PostalCode>" . $this->shipfromPostalCode . "</PostalCode>
    	<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
      </Address>
    </ShipFrom>";
        $data .= "<AlternateDeliveryAddress>
<Address>";
        if($this->shiptoCity != '') {
            $data .= "<City>" . $this->shiptoCity . "</City>";
        }
        $data .= "<StateProvinceCode>" . $this->shiptoStateProvinceCode . "</StateProvinceCode>
        <PostalCode>" . $this->shiptoPostalCode . "</PostalCode>
        <CountryCode>" . $this->shiptoCountryCode . "</CountryCode>";
        /*if ($this->residentialAddress == 1) {
            $data .= "<ResidentialAddressIndicator/>";
        }*/
        $data .= "</Address>
</AlternateDeliveryAddress>";
        $data .= "<ShipmentIndicationType><Code>". $this->shipmentIndicationType ."</Code></ShipmentIndicationType>";
        $weightSum = 0;

        foreach ($this->packages as $key => $pv) {
            $data .= "<Package>
      <PackagingType>
        <Code>" . $pv["packagingtypecode"] . "</Code>
      </PackagingType>";
            $data .= array_key_exists('additionalhandling', $pv) ? $pv['additionalhandling'] : '';
            if (isset($pv['length'])
                && isset($pv['width'])
                && isset($pv['height'])
                && strlen($pv['length']) > 0
                && strlen($pv['width']) > 0
                && strlen($pv['height']) > 0) {
                $data .= "<Dimensions>
<UnitOfMeasurement>
<Code>" . $this->unitOfMeasurement . "</Code>";
                $data .= "</UnitOfMeasurement>";
                $data .= "<Length>" . ($pv['length'] * $this->dimentionUnitKoef) . "</Length>
<Width>" . ($pv['width'] * $this->dimentionUnitKoef) . "</Width>
<Height>" . ($pv['height'] * $this->dimentionUnitKoef) . "</Height>";
                $data .= "</Dimensions>";
            }
            $data .= "<PackageWeight>
        <UnitOfMeasurement>
            <Code>" . $this->weightUnits . "</Code>";
            $packweight = array_key_exists('packweight', $pv) ? $pv['packweight'] : '';
            $weight = array_key_exists('weight', $pv) ? $pv['weight'] : '';
            $weight = round(($weight + (is_numeric(str_replace(',', '.', $packweight)) ? $packweight : 0)), 1);
            if ($weight < 0.1) {
                $weight = 0.1;
            }
            $weightSum += $weight;
            $data .= "</UnitOfMeasurement>
        <Weight>" . $weight . "</Weight>";
            if ($this->isLargePackage($pv, $this->weightUnits, $this->unitOfMeasurement, $weight) === true) {
                $data .= "<LargePackageIndicator/>";
            }

            $data .= "</PackageWeight>";

            $data .= "<PackageServiceOptions>";
            if ($this->insured == 1 && $key == 0) {
                $data .= "<InsuredValue>
                <CurrencyCode>" . $this->currency . "</CurrencyCode>
                <MonetaryValue>" . $this->invoiceLineTotal . "</MonetaryValue>
                </InsuredValue>";
            }

            if ($this->isAdult('P')) {
                $data .= "<DeliveryConfirmation><DCISType>" . $this->adult . "</DCISType></DeliveryConfirmation>";
            }

            $data .= "</PackageServiceOptions>";

            $data .= "</Package>";
        }

        if ($this->isAdult('S')) {
            $data .= "<ShipmentServiceOptions>";
            $data .= "<DeliveryConfirmation><DCISType>" . $this->adult . "</DCISType></DeliveryConfirmation>";
            $data .= "</ShipmentServiceOptions>";
        }

        $data .= "</Shipment></RatingServiceSelectionRequest>";

        $cie = 'onlinetools';
        $curl = $this->_handy->_conf;
        $result = $curl->curlSetOption('https://' . $cie . '.ups.com/ups.app/xml/Rate', $data);
        $result = strstr($result, '<?xml');
        //return $data;
        $xml = $this->xml2array(simplexml_load_string($result));
        if ($this->_handy->_conf->getStoreConfig('carriers/upsap/debug') == 1) {
            $this->_handy->_conf->log($data);
            $this->_handy->_conf->log($result);
        }

        if ($xml['Response']['ResponseStatusCode'] == 1) {
            $rates = [];
            $timeInTransit = null;
            if (!isset($xml['RatedShipment'][0])) {
                $xml['RatedShipment'] = [$xml['RatedShipment']];
            }

            foreach ($xml['RatedShipment'] as $rated) {
                if (isset($rated['Service'])) {
                    $rateCode = (string)$rated['Service']['Code'];

                    $rates[$rateCode]["def"] = [
                        'price' => $rated['TotalCharges']['MonetaryValue'],
                        'currency' => $rated['TotalCharges']['CurrencyCode'],
                    ];

                    if (isset($rated['TotalChargesWithTaxes'])) {
                        $totalChargesWithTaxes = $rated['TotalChargesWithTaxes'];
                        if (isset($totalChargesWithTaxes[0])) {
                            $totalChargesWithTaxes = $totalChargesWithTaxes[0];
                        }

                        if (isset($totalChargesWithTaxes) && isset($totalChargesWithTaxes['CurrencyCode'])
                            && isset($totalChargesWithTaxes['MonetaryValue'])
                        ) {
                            $rates[$rateCode]["deftax"] = [
                                'price' => $totalChargesWithTaxes['MonetaryValue'],
                                'currency' => $totalChargesWithTaxes['CurrencyCode'],
                            ];
                        }
                    }


                    if (isset($rated['NegotiatedRates'])) {
                        $defaultPrice = $rated['NegotiatedRates'];
                        if (isset($defaultPrice[0])) {
                            $defaultPrice = $defaultPrice[0];
                        }

                        $defaultPrice = $defaultPrice['NetSummaryCharges'];
                        if (isset($defaultPrice[0])) {
                            $defaultPrice = $defaultPrice[0];
                        }

                        if (isset($defaultPrice['GrandTotal'][0])) {
                            $rates[$rateCode]["nr"] = [
                                'price' => $defaultPrice['GrandTotal'][0]['MonetaryValue'],
                                'currency' => $defaultPrice['GrandTotal'][0]['CurrencyCode'],
                            ];
                        } else {
                            $rates[$rateCode]["nr"] = [
                                'price' => $defaultPrice['GrandTotal']['MonetaryValue'],
                                'currency' => $defaultPrice['GrandTotal']['CurrencyCode'],
                            ];
                        }



                        if (isset($defaultPrice['TotalChargesWithTaxes'])) {
                            $defaultPrice = $defaultPrice['TotalChargesWithTaxes'];
                            if (isset($defaultPrice[0])) {
                                $defaultPrice = $defaultPrice[0];
                            }

                            $rates[$rateCode]["nrtax"] = [
                                'price' => $defaultPrice['MonetaryValue'],
                                'currency' => $defaultPrice['CurrencyCode'],
                            ];
                        }
                    }

                    if ($timeInTransit === null) {
                        $timeInTransit = $this->timeInTransit($weightSum);
                    }

                    if (is_array($timeInTransit) && isset($timeInTransit['days'][$rateCode])) {
                        $rates[$rateCode]['day'] = $timeInTransit['days'][$rateCode];
                    }
                }
            }

            return $rates;
        } else {
            $this->_handy->_conf->log($data);
            $this->_handy->_conf->log($result);
            $errorDescription = [];
            if(is_array($xml['Response']['Error']) && isset($xml['Response']['Error'][0])){
                foreach ($xml['Response']['Error'] as $item) {
                    $errorDescription[] = $item['ErrorDescription'];
                }
            } else {
                $errorDescription[] = $xml['Response']['Error']['ErrorDescription'];
            }

            $error = ['error' => implode('; ', $errorDescription)];
            return $error;
        }
    }

    public function isLargePackage($package, $weightUnit, $dimensionUnit, $weight)
    {
        $dimensionKoef = 1;
        if ($dimensionUnit == 'CM') {
            $dimensionKoef = 2.54;
        }

        if (isset($package['length'])
            && isset($package['width'])
            && isset($package['height'])
            && strlen($package['length']) > 0
            && strlen($package['width']) > 0
            && strlen($package['height']) > 0) {
            $maxSide = max($package['width'], $package['height'], $package['length']);

            $dimArray = [$package['width'], $package['height'], $package['height']];
            rsort($dimArray);


            if ($dimArray[0] > 96 * $dimensionKoef
                || ($dimArray[0] + (2 * $dimArray[1] + 2 * $dimArray[2]) > 96 * $dimensionKoef)) {
                return true;
            }
        }

        if (($weight > 40 && $weightUnit == 'KGS') || ($weight > 90 && $weightUnit == 'LBS')) {
            return true;
        }

        return false;
    }

    public function timeInTransit($weightSum)
    {
        $cie = 'onlinetools';
        $data = "<?xml version=\"1.0\" ?>
<AccessRequest xml:lang='en-US'>
<AccessLicenseNumber>" . $this->AccessLicenseNumber . "</AccessLicenseNumber>
<UserId>" . $this->UserID . "</UserId>
<Password>" . $this->Password . "</Password>
</AccessRequest>
<?xml version=\"1.0\" ?>
<TimeInTransitRequest xml:lang='en-US'>
<Request>
<TransactionReference>
<CustomerContext>Shipper</CustomerContext>
<XpciVersion>1.0002</XpciVersion>
</TransactionReference>
<RequestAction>TimeInTransit</RequestAction>
</Request>
<TransitFrom>
<AddressArtifactFormat>
<PoliticalDivision2>" . $this->shipfromCity . "</PoliticalDivision2>
<PoliticalDivision1>" . $this->shipfromStateProvinceCode . "</PoliticalDivision1>
<CountryCode>" . $this->shipfromCountryCode . "</CountryCode>
<PostcodePrimaryLow>" . $this->shipfromPostalCode . "</PostcodePrimaryLow>
</AddressArtifactFormat>
</TransitFrom>
<TransitTo>
<AddressArtifactFormat>
<PoliticalDivision2>" . $this->shiptoCity . "</PoliticalDivision2>
<PoliticalDivision1>" . $this->shiptoStateProvinceCode . "</PoliticalDivision1>
<CountryCode>" . $this->shiptoCountryCode . "</CountryCode>
<PostcodePrimaryLow>" . $this->shiptoPostalCode . "</PostcodePrimaryLow>
</AddressArtifactFormat>
</TransitTo>
<ShipmentWeight>
<UnitOfMeasurement>
<Code>" . $this->weightUnits . "</Code>
</UnitOfMeasurement>
<Weight>" . $weightSum . "</Weight>
</ShipmentWeight>
<PickupDate>" . (!empty($this->pickupDate)?($this->pickupDate):date('Ymd')) . "</PickupDate>
<DocumentsOnlyIndicator />";
        if ($this->shiptoCountryCode != $this->shipfromCountryCode) {
            $data .= "<InvoiceLineTotal><MonetaryValue>" . $this->invoiceLineTotal . "</MonetaryValue><CurrencyCode>" . $this->currency . "</CurrencyCode></InvoiceLineTotal>";
        }

        $data .= "</TimeInTransitRequest>";
        $curl = $this->_handy->_conf;
        $result = $curl->curlSend('https://' . $cie . '.ups.com/ups.app/xml/TimeInTransit', $data);

        if($this->_handy->_conf->getStoreConfig('carriers/ups/debug') == 1){
            $this->_handy->_conf->log($data);
            $this->_handy->_conf->log($result);
        }

        if (!$curl->error) {
            $xml = $this->xml2array(simplexml_load_string($result));
            if ($xml['Response']['ResponseStatusCode'] == 0 || $xml['Response']['ResponseStatusDescription'] != 'Success') {
                return ['error' => 1];
            } else {
                $countDay = [];
                if (isset($xml['TransitResponse'])) {
                    foreach ($xml['TransitResponse']['ServiceSummary'] as $v) {
                        $codes = $this->_handy->_conf->getUpsCode($v['Service']['Code']);
                        if (!is_array($codes)) {
                            $codes = [$codes];
                        }

                        if (isset($v['EstimatedArrival']['TotalTransitDays'])) {
                            foreach ($codes as $v2) {
                                $countDay[$v2]['days'] = $v['EstimatedArrival']['TotalTransitDays'];
                                $countDay[$v2]['datetime']['date'] = $v['EstimatedArrival']['Date'];
                                $countDay[$v2]['datetime']['time'] = $v['EstimatedArrival']['Time'];
                            }

                        } else if (isset($v['EstimatedArrival']['BusinessTransitDays'])) {
                            foreach ($codes as $v2) {
                                $countDay[$v2]['days'] = $v['EstimatedArrival']['BusinessTransitDays'];
                                $countDay[$v2]['datetime']['date'] = $v['EstimatedArrival']['Date'];
                                $countDay[$v2]['datetime']['time'] = $v['EstimatedArrival']['Time'];
                            }
                        }
                    }
                }

                return ['error' => 0, 'days' => $countDay];
            }
        } else {
            return $result;
        }
    }

    public function xml2array($xmlObject)
    {
        $outString = json_encode($xmlObject);
        unset($xmlObject);
        return json_decode($outString, true);
    }

    protected function isAdult($typeService)
    {
        if ($this->adult == 4) {
            if ($typeService === "P") {
                return false;
            } else if ($typeService === "S") {
                return true;
            }
        }

        if ($typeService === "S") {
            $this->adult = $this->adult - 1;
        }

        if ($this->adult <= 0) {
            return false;
        }

        $adult = 'DC';
        if ($typeService === 'P') {
            if ($this->adult == 2) {
                $adult = 'DC-SR';
            } else if ($this->adult == 3) {
                $adult = 'DC-ASR';
            }
        } else if ($typeService === 'S') {
            if ($this->adult == 1) {
                $adult = 'DC-SR';
            } else if ($this->adult == 2) {
                $adult = 'DC-ASR';
            }
        }

        switch ($this->shipfromCountryCode) {
            case 'US':
            case 'CA':
            case 'PR':
                switch ($this->shiptoCountryCode) {
                    case 'US':
                    case 'PR':
                        if ($typeService === 'P') {
                            return true;
                        }

                        break;
                    default:
                        if ($typeService === 'S' && ($adult === 'DC-SR' || $adult === 'DC-ASR')) {
                            return true;
                        }

                        break;
                }

                break;
            default:
                if ($typeService === 'S' && ($adult === 'DC-SR' || $adult === 'DC-ASR')) {
                    return true;
                }

                break;
        }

        return false;
    }
}
