<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Index;

class Accesspointcallback extends \Infomodus\Upsap\Controller\Adminhtml\Index
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $url = $this->getRequest()->getParams();
        $content = '<!DOCTYPE html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        </head>
        <body>';
        if (isset($url['action'])) {
            $content .= '<script type="text/javascript">
            window.onload = function () {';

            if (isset($url['action']) && $url['action'] == "cancel") {
                $content .= 'window.top.closePopapMapRVA();';
            }
            if (isset($url['action']) && $url['action'] == "select") {
                $arrUrl = array();
                foreach ($url AS $k => $v) {
                    $arrUrl[$k] = $v;
                }
                $content .= 'window.top.setAccessPointToCheckout(' . json_encode($arrUrl) . ');';
            }
            $content .= '}</script>';
        } else {
            $content .= 'Error cross origin or 302 Redirect<script type="text/javascript">setTimeout(function(){window.top.closePopapMapRVA();}, 7000)</script>';
        }
        $content .= '</body>
        </html>';
        $this->getResponse()
            ->setContent($content);

        return;
    }
}
