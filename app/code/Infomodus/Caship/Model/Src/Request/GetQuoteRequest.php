<?php

namespace Infomodus\Caship\Model\Src\Request;
class GetQuoteRequest extends AbstractRequest
{
    protected $required = array(
        'From' => null,
        'BkgDetails' => null,
        'To' => null,
        'Dutiable' => null
    );

    protected function buildRoot()
    {
        $root = $this->xml->createElementNS("http://www.dhl.com", 'p:DCTRequest');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:p1', 'http://www.dhl.com/datatypes');
        $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:p2', 'http://www.dhl.com/DCTRequestdatatypes');
        $root->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'http://www.dhl.com DCT-req.xsd '
        );

        $this->currentRoot = $this->xml->appendChild($root);

        return $this;
    }

    protected function buildRequestType()
    {
        $type = $this->buildElement('GetQuote');
        $this->currentRoot = $this->currentRoot->appendChild($type);

        return $this;
    }

    /**
     * @param CL\PhpDhl\Request\Partials\Location $from Origin address of the shipment
     */
    public function setFrom($from)
    {
        $this->required['From'] = $from;

        return $this;
    }

    /**
     * @param CL\PhpDhl\Request\Partials\Location $to Destination address of the shipment
     */
    public function setTo($to)
    {
        $this->required['To'] = $to;

        return $this;
    }

    /**
     * @param Partials\BkgDetails $bkgDetails Shipment details
     */
    public function setBkgDetails($bkgDetails)
    {
        $this->required['BkgDetails'] = $bkgDetails;

        return $this;
    }

    /**
     * @param string $tag
     */
    private function buildLocation($tag, $countryCode, $postalCode = null, $city = null)
    {
        $location = new \Infomodus\Caship\Model\Src\Request\Partials\Location();
        $location->setCountryCode($countryCode)
            ->setPostalCode($postalCode)
            ->setCity($city);

        $setter = "set$tag";

        return $this->$setter($location);
    }

    public function buildFrom($countryCode, $postalCode = null, $city = null)
    {
        return $this->buildLocation('From', $countryCode, $postalCode, $city);
    }

    public function buildTo($countryCode, $postalCode = null, $city = null)
    {
        return $this->buildLocation('To', $countryCode, $postalCode, $city);
    }

    /**
     * Add details of the shippment
     * @param string $paymentCountryCode
     * @param DateTime $date
     * @param array $pieces Each piece element is an array with height, width, depth and weight keys
     */
    public function buildBkgDetails(
        $paymentCountryCode,
        $date,
        array $pieces,
        $readyTime = 'PT10H00M',
        $dimensionUnit = 'CM',
        $weightUnit = 'KG',
        $isDutiable = false,
        $paymentAccountNumber,
        $insuredValue = null,
        $insuredCurrency = null
    )
    {
        $bkgDetails = new \Infomodus\Caship\Model\Src\Request\Partials\BkgDetails();

        $bkgDetails->setPaymentCountryCode($paymentCountryCode)
            ->setDate($date)
            ->setReadyTime($readyTime)
            ->setDimensionUnit($dimensionUnit)
            ->setWeightUnit($weightUnit)
            ->setPaymentAccountNumber($paymentAccountNumber)
            ->setIsDutiable($isDutiable);

        if ($insuredValue !== null) {
            $bkgDetails->setInsured($insuredValue, $insuredCurrency);
        }

        $pieceId = 0;
        foreach ($pieces as $pieceData) {
            $piece = new \Infomodus\Caship\Model\Src\Request\Partials\PieceQuote();
            $piece->setPieceId(++$pieceId);
            if (isset($pieceData['height'])) {
                $piece->setHeight($pieceData['height']);
            }

            if (isset($pieceData['depth'])) {
                $piece->setDepth($pieceData['depth']);
            }

            if (isset($pieceData['width'])) {
                $piece->setWidth($pieceData['width']);
            }

            $piece->setWeight($pieceData['weight']);
            $bkgDetails->addPiece($piece);
        }

        return $this->setBkgDetails($bkgDetails);
    }

    public function buildDutiable($declaredValue, $declaredCurrency)
    {
        $dutiable = new \Infomodus\Caship\Model\Src\Request\Partials\DutiableQuot();
        $dutiable->setDeclaredValue($declaredValue);
        $dutiable->setDeclaredCurrency($declaredCurrency);

        return $this->setDutiable($dutiable);
    }

    public function setDutiable(\Infomodus\Caship\Model\Src\Request\Partials\DutiableQuot $dutiable)
    {
        $this->required['Dutiable'] = $dutiable;

        return $this;
    }
}
