<?php
namespace Infomodus\Caship\Model;
use Infomodus\Caship\Helper\Config;
use Infomodus\Caship\Model\Src\Request\GetQuoteRequest;
use Infomodus\Caship\Model\Src\Response\GetQuoteResponse;

class Dhl
{
    public $UserID;
    public $Password;
    public $shipperNumber;
    public $packages;
    public $weightUnits;
    public $packageWeight;

    public $shipperCity;
    public $shipperPostalCode;
    public $shipperCountryCode;

    public $shiptoCity;
    public $shiptoPostalCode;
    public $shiptoCountryCode;

    public $declaredValue;
    public $currencyCode;
    public $insured_automaticaly = null;

    public $testing;

    protected $request;
    protected $_conf;

    public function __construct(
        Config $config
    )
    {
        $this->_conf = $config;
    }

    function getShipPrice($isDutiable = false)
    {
        $request = new GetQuoteRequest(
            $this->UserID,
            $this->Password
        );

        $destWeightUnit = $this->_conf->getWeightUnitByCountry($this->shiptoCountryCode);
        $weingUnitKoef = 1;
        switch ($destWeightUnit) {
            case 'KG':
                $destWeightUnit = 'K';
                break;
            case 'LB':
                $destWeightUnit = 'L';
                break;
        }

        if ($destWeightUnit != $this->weightUnits) {
            if ($destWeightUnit == 'L') {
                $weingUnitKoef = 2.2046;
            } else {
                $weingUnitKoef = 1 / 2.2046;
            }
        }

        $destDimentionUnit = $this->_conf->getDimensionUnitByCountry($this->shiptoCountryCode);
        switch ($destDimentionUnit) {
            case 'CM':
                $destDimentionUnit = 'C';
                break;
            case 'IN':
                $destDimentionUnit = 'I';
                break;
        }

        $dimentionUnitKoef = 1;
        if ($destWeightUnit != $this->weightUnits) {
            if ($destDimentionUnit == 'C') {
                $dimentionUnitKoef = 2.54;
            } else {
                $dimentionUnitKoef = 1 / 2.54;
            }
        }

        $pieces = array();
        foreach ($this->packages as $pv) {
            $pieces1 = [];
            if (isset($pv['height']) || isset($pv['width']) || isset($pv['length'])) {
                $pieces1['height'] = round($pv['height'] * $dimentionUnitKoef, 0);
                $pieces1['width'] = round($pv['width'] * $dimentionUnitKoef, 0);
                $pieces1['depth'] = round($pv['length'] * $dimentionUnitKoef, 0);
            }

            $packweight = array_key_exists('packweight', $pv) ? (float)str_replace(',', '.', $pv['packweight']) * $weingUnitKoef : 0;
            $weight = array_key_exists('weight', $pv) ? (float)str_replace(',', '.', $pv['weight']) * $weingUnitKoef : 0;
            $pieces1['weight'] = round(($weight + (is_numeric($packweight) ? $packweight : 0)), 3);
            $pieces[] = $pieces1;
        }

        $insuredAmount = null;
        $insuredCurrency = null;
        if($this->insured_automaticaly) {
            $insuredAmount = $this->declaredValue;
            $insuredCurrency = $this->currencyCode;
        }

        $request->buildFrom($this->shipperCountryCode, $this->shipperPostalCode, $this->shipperCity)
            ->buildBkgDetails($this->shipperCountryCode, new \DateTime('now'),
                $pieces, 'PT10H21M', ($destDimentionUnit == 'C' ? 'CM' : 'IN'), ($destWeightUnit == 'K' ? 'KG' : 'LB'), $isDutiable, $this->shipperNumber, $insuredAmount, $insuredCurrency)
            ->buildTo($this->shiptoCountryCode, $this->shiptoPostalCode, $this->shiptoCity);
        if ($isDutiable) {
            $request->buildDutiable($this->declaredValue, $this->currencyCode);
        }

        $responseData = $request->send($this->testing);
        $response = new GetQuoteResponse($responseData);
        if($this->_conf->getStoreConfig('carriers/caship/debug') == 1){
            $this->_conf->log($request->__toString());
            $this->_conf->log($responseData);
        }
        return $response->getPrices();
    }
}