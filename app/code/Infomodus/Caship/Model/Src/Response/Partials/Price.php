<?php
/**
 * @author    Danail Kyosev <ddkyosev@gmail.com>
 * @copyright 2014, Clippings Ltd.
 * @license   http://spdx.org/licenses/BSD-3-Clause
 */
namespace Infomodus\Caship\Model\Src\Response\Partials;
class Price
{
    private $productGlobalCode;
    private $productLocalCode;
    private $productName;
    private $currencyCode;
    private $weightCharge;
    private $weightChargeTax;
    private $totalAmmount;
    private $totalTaxAmmount;

    public function getProductGlobalCode()
    {
        return $this->productGlobalCode;
    }
    public function getProductLocalCode()
    {
        return $this->productLocalCode;
    }
    public function getProductName()
    {
        return $this->productName;
    }

    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    public function getWeightCharge()
    {
        return $this->weightCharge;
    }

    public function getWeightChargeTax()
    {
        return $this->weightChargeTax;
    }

    public function getTotalAmount()
    {
        return $this->totalAmmount;
    }

    public function getTotalTaxAmount()
    {
        return $this->totalTaxAmmount;
    }

    public function __construct($data)
    {
        $this->productGlobalCode = (string) $data->GlobalProductCode;
        $this->productLocalCode = (string) $data->LocalProductCode;
        $this->productName = (string) $data->ProductShortName;
        $this->currencyCode = (string) $data->CurrencyCode;
        $this->weightCharge = (string) $data->WeightCharge;
        $this->weightChargeTax = (string) $data->WeightChargeTax;
        $this->totalAmmount = (string) $data->ShippingCharge;
        $this->totalTaxAmmount = (string) $data->TotalTaxAmount;
    }
}
