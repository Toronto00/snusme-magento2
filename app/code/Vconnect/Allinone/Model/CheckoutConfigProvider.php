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
 * @class Vconnect_AllInOne_Model_Carrier_Postnord
 */

namespace Vconnect\Allinone\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Vconnect\Allinone\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * CheckoutConfigProvider constructor.
     * @param \Extensa\Intime\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $result = [];

        $result['vconnect_allinone']['get_postnord_html_url'] = $this->dataHelper->getUrl('vconnect_allinone/postnord/getPostnordHtml', array('_secure' => true));
        $result['vconnect_allinone']['get_additionalfee_title_url'] = $this->dataHelper->getUrl('vconnect_allinone/postnord/getAdditionalfeeTitle', array('_secure' => true));

        return $result;
    }
}
