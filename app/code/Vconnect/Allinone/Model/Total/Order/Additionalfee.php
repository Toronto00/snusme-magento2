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

namespace Vconnect\Allinone\Model\Total\Order;

class Additionalfee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null; 

    protected $dataHelper;

    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Vconnect\Allinone\Helper\Data $dataHelper
    )
    {
        $this->quoteValidator = $quoteValidator;
        $this->dataHelper = $dataHelper;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$quote->getShippingAddress() || !$quote->getShippingAddress()->getQuoteId() || $shippingAssignment->getShipping()->getAddress()->getAddressType() != 'shipping' || !count($shippingAssignment->getItems()) || ($quote && $this->dataHelper->getAdditionalfeeAmount() === false)) {
            return $this;
        }

        $additionalfee = $this->dataHelper->getPriceFormated($quote, (float)$this->dataHelper->getAdditionalfeeAmount(), false, false);
        $additionalfee_base = $this->dataHelper->getAdditionalfeeAmount();

        $total->setTotalAmount('additionalfee', $additionalfee);
        $total->setBaseTotalAmount('additionalfee', $additionalfee_base);

        $total->setAdditionalfee($additionalfee);
        $total->setBaseAdditionalfee($additionalfee_base);

        $total->setGrandTotal($total->getGrandTotal() + $additionalfee);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $additionalfee_base);

        return $this;
    }

    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $address = $quote->getShippingAddress();
        if ($address->getAddressType() != 'shipping' || $this->dataHelper->getAdditionalfeeAmount() === false) {
            return [];
        }

        $additionalfee = $this->dataHelper->getPriceFormated($quote, (float)$this->dataHelper->getAdditionalfeeAmount(), false, false);
        $additionalfee_base = (float)$this->dataHelper->getAdditionalfeeAmount();

        return [
            'code' => 'additionalfee',
            'title' => $this->dataHelper->getAdditionalfeeTitle(),
            'value' => $additionalfee,
            'base_value' => $additionalfee_base,
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return $this->dataHelper->getAdditionalfeeTitle();
    }
}
