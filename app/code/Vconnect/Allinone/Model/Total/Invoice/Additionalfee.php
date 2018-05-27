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

namespace Vconnect\Allinone\Model\Total\Invoice;

class Additionalfee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    protected $dataHelper;

    public function __construct(
        \Vconnect\Allinone\Helper\Data $dataHelper
    )
    {
        $this->dataHelper = $dataHelper;
    }

    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        if ($invoice->getOrder()->hasInvoices() != 0 || !$invoice->getOrder()->getVconnectPostnordData() || strpos($invoice->getOrder()->getShippingMethod(), 'vconnectpostnord') === false || (strpos($invoice->getOrder()->getShippingMethod(), 'privatehome') !== false)) {
            return $this;
        }

        $additionalfee = $this->dataHelper->getPriceFormatedByOrder($invoice->getOrder(), (float)$this->dataHelper->getAdditionalfeeAmount($invoice->getOrder()), false, false);
        $additionalfee_base = $this->dataHelper->getAdditionalfeeAmount($invoice->getOrder());

        if (!$additionalfee) {
            return $this;
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $additionalfee);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $additionalfee_base);

        return $this;
    }
}
