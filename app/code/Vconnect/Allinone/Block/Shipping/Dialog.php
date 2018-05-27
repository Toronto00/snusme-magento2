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
 * @class Vconnect_AllInOne_Block_Shipping_Dialog
 */

namespace Vconnect\Allinone\Block\Shipping;

use Magento\Framework\App\Area;

class Dialog extends \Magento\Framework\View\Element\Template
{
    protected $context;
    
    /**
     * @var \Vconnect\Allinone\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendQuoteSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     *
     * @var array 
     */
    protected $_rates;

    /**
     * @var \Vconnect\Allinone\Api\Data\RateInterfaceFactory
     */
    protected $allinoneRateFactory;

    /**
     * @var \Vconnect\Allinone\Model\Carrier\PostnordFactory $modelCarrierPostnordFactory
     */
    protected $modelCarrierPostnordFactory;

    /**
     * Allinone constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context,
     * @param \Vconnect\Allinone\Helper\Data $dataHelper,
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession,
     * @param \Magento\Checkout\Model\Session $checkoutSession,
     * @param \Magento\Framework\View\Layout $layout,
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper,
     * @param \Vconnect\Allinone\Api\Data\RateInterfaceFactory $allinoneRateFactory,
     * @param \Vconnect\Allinone\Model\Carrier\PostnordFactory $modelCarrierPostnordFactory,
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Vconnect\Allinone\Api\Data\RateInterfaceFactory $allinoneRateFactory,
        \Vconnect\Allinone\Model\Carrier\PostnordFactory $modelCarrierPostnordFactory,
        array $data = []
    ) {
        $this->context = $context;
        $this->dataHelper = $dataHelper;
        $this->backendQuoteSession = $backendQuoteSession;
        $this->checkoutSession = $checkoutSession;
        $this->layout = $layout;
        $this->jsonHelper = $jsonHelper;
        $this->allinoneRateFactory = $allinoneRateFactory;
        $this->modelCarrierPostnordFactory = $modelCarrierPostnordFactory;

        parent::__construct($context, $data);

        $templateTypePath = 'embedded';

        if ($this->context->getAppState()->getAreaCode() == Area::AREA_ADMINHTML) {
           $templateTypePath = 'aio_base_createorder';
        }
        $this->setTemplate("vconnect/$templateTypePath/base.phtml");

        if ($this->context->getAppState()->getAreaCode() == Area::AREA_ADMINHTML) {
            $this->setBlockQuote($this->backendQuoteSession->getQuote());
        } else {
            $this->setBlockQuote($this->checkoutSession->getQuote());
        }

        $this->setDataHelper($dataHelper);
    }

    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * 
     * @param Magento\Framework\View\Element\Template $block
     * @return Vconnect_AllInOne_Block_Shipping_Dialog
     */
    public function addTabHeader(\Magento\Framework\View\Element\Template $block)
    {
        $this->getChildBlock('tab_headers')->append($block);
        return $this;
    }

    /**
     * 
     * @param Magento\Framework\View\Element\Template $block
     * @return Vconnect_AllInOne_Block_Shipping_Dialog
     */
    public function addTabScripts(\Magento\Framework\View\Element\Template $block)
    {
        $this->getChildBlock('body_scripts')->append($block);
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getRates()
    {
        if (!$this->_rates) {
            $this->_rates = array();
            $quote = $this->getBlockQuote();
            if ($quote && $quote->getId() && $quote->getShippingAddress()->getId()) {
                $collection = $this->allinoneRateFactory->create()->getCollection();
                $collection->addFieldToFilter('quote_id', array('eq' => $quote->getId()));
                $collection->addFieldToFilter('address_id', array('eq' => $quote->getShippingAddress()->getId()));
                $allinoneRateData = $collection->getFirstItem();
                if ($allinoneRateData->getId() && $allinoneRateData->getRateData()) {
                    $this->_rates = unserialize($allinoneRateData->getRateData());

                    usort($this->_rates, function($a, $b) {
                        return (int)$a['sort_order'] - (int)$b['sort_order'];
                    });
                }
            }
        }
        return $this->_rates;
    }
    
    public function getChildrenHtml($name) {
        $layout = $this->getLayout();

        $html = '';

        $children = $this->getChildBlock($name)->getChildNames($name);
        foreach ($children as $child) {
            $html .= $layout->renderElement($child);
        }

        return $html;
    }

    /**
     * @return string
     */
    public function getPostnordPointsUrl()
    {
        return $this->getUrl('vconnect_allinone/postnord/points', array('_secure' => true));
    }

    /**
     * @return string
     */
    public function getPostnordTransitUrl()
    {
        return $this->getUrl('vconnect_allinone/postnord/transit', array('_secure' => true));
    }

    /**
     * @return string
     */
    public function getPostnordDataUrl()
    {
        return $this->getUrl('vconnect_allinone/postnord/saveData', array('_secure' => true));
    }
}
