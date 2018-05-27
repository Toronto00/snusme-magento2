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
 * @class Vconnect_AllInOne_Helper_Data
 */

namespace Vconnect\Allinone\Observer\Core;

use Magento\Framework\App\Area;

class LayoutRenderElement implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Vconnect\Allinone\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Framework\Event\Manager $eventManager
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * SubmitAllAfter constructor.
     * @param \Vconnect\Allinone\Helper\Data $dataHelper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct (
        \Vconnect\Allinone\Helper\Data $dataHelper,
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\App\State $state
    ) {
        $this->dataHelper = $dataHelper;
        $this->layout = $layout;
        $this->eventManager = $eventManager;
        $this->state = $state;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getEvent()->getLayout();
        $elementName = $observer->getEvent()->getElementName();
        /** @var \Magento\Framework\View\Element\AbstractBlock $block */
        $block = $layout->getBlock($elementName);

        if ($this->dataHelper->getStoreConfigFlag('carriers/vconnectpostnord/active') && $block instanceof \Vconnect\Allinone\Block\Adminhtml\Order\Create\Shipping\Method\Form) {
            $transport = $observer->getEvent()->getTransport();
            $output = $transport->getData('output');

            if (stripos($output, 'vconnectpostnord') !== false) {
                $_block = $this->layout->createBlock(
                    'Vconnect\Allinone\Block\Shipping\Dialog', 'allinone_dialog'
                );

                $blockTabHeaders = $this->layout->createBlock(
                    'Magento\Framework\View\Element\Template', 'tab_headers'
                );
                $_block->setChild('tab_headers', $blockTabHeaders);

                $blockBodyScripts = $this->layout->createBlock(
                    'Magento\Framework\View\Element\Template', 'body_scripts'
                );
                $_block->setChild('body_scripts', $blockBodyScripts);

                $this->shippingMethodsUiCollect($_block);

                $newHtml = $_block->toHtml();
                $output .= $newHtml;

                $transport->setData('output', $output);
            }
        }
    }

    public function shippingMethodsUiCollect($block) {
        $rates = $block->getRates();
        foreach ($rates as $data){
            if(!isset($data['vc_method_data']) || !$data['vc_method_data']){
                continue;
            }
            $_data = json_decode($data['vc_method_data'],true);
            $countryCode    = strtolower($_data['country']);
            $methodCode     = $_data['method'];
            $templatePath   = $_data['template'];

            foreach (array('tabHeader','tabScripts') as $template){
                $b = $this->layout->createBlock(
                    'Magento\Framework\View\Element\Template', $templatePath.'_'.$template
                );
                $b->setData('allinone_data', $data);
                $b->setData('allinone_config', $_data);
                $b->setData('module_name', 'Vconnect_Allinone');
                $b->setDataHelper($this->dataHelper);
                $b->setBlockQuote($block->getBlockQuote());

                $templateTypePath = 'embedded';

                if ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
                   $templateTypePath = 'aio_base_createorder';
                }
                $b->setTemplate("vconnect/$templateTypePath/$templatePath/$template.phtml");
                $templateFile = $b->getTemplateFile();
                if (!file_exists($templateFile)) {
                    continue;
                }
                $action = sprintf('add%s',  ucfirst($template));
                if(is_callable(array($block, $action))){
                    $block->$action($b);
                }
            }
        }
    }
}
