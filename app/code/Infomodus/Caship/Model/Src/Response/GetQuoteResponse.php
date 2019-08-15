<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Caship\Model\Src\Response;
use Infomodus\Caship\Model\Src\Response\Partials\Price;

class GetQuoteResponse
{
    private $parsed;
    private $prices;

    /**
     * A quote response can have more that one shipping option with different price rates
     * @param Partials\Price[] $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;

        return $this;
    }

    /**
     * A quote response can have more that one shipping option with different price rates
     * @return Partials\Price[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Parses a quote response XML and provides price information
     * @param string $data XML response from GetQuoteRequest
     * @todo add error response handling
     */
    public function __construct($data)
    {
        $this->parsed = simplexml_load_string(utf8_encode($data));

        $this->populatePrices();
    }

    private function populatePrices()
    {
        if(isset($this->parsed,
            $this->parsed->GetQuoteResponse,
            $this->parsed->GetQuoteResponse->BkgDetails,
            $this->parsed->GetQuoteResponse->BkgDetails->QtdShp)) {
            $qtdShp = $this->parsed->GetQuoteResponse->BkgDetails->QtdShp;
            $prices = array();
            if(isset($qtdShp) && !empty($qtdShp)) {
                foreach ($qtdShp as $type) {
                    /*if ($type->TransInd == 'Y') {*/
                    $prices[] = new Price($type);
                    $this->setPrices($prices);
                    /*}*/
                }
            }
        }
    }
}
