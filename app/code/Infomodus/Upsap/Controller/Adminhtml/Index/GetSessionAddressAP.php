<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Index;

class GetSessionAddressAP extends \Infomodus\Upsap\Controller\Adminhtml\Index
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        $session = $this->_checkoutSession;
        $address = [];
        if ($session->getUpsapAddLine1()) {
            $address['addLine1'] = $session->getUpsapAddLine1();

            if ($session->getUpsapAddLine2()) {
                $address['addLine2'] = $session->getUpsapAddLine2();
            }

            if ($session->getUpsapAddLine3()) {
                $address['addLine3'] = $session->getUpsapAddLine3();
            }

            if ($session->getUpsapCity()) {
                $address['city'] = $session->getUpsapCity();
            }

            if ($session->getUpsapCountry()) {
                $address['country'] = $session->getUpsapCountry();
            }

            if ($session->getUpsapFax()) {
                $address['fax'] = $session->getUpsapFax();
            }

            if ($session->getUpsapState()) {
                $address['state'] = $session->getUpsapState();
            }

            if ($session->getUpsapPostal()) {
                $address['postal'] = $session->getUpsapPostal();
            }

            if ($session->getUpsapAppuId()) {
                $address['appuId'] = $session->getUpsapAppuId();
            }

            if ($session->getUpsapName()) {
                $address['name'] = $session->getUpsapName();
            }

            return $result->setData($address);
        } else {
            return $result->setData(["error" => "empty"]);
        }
    }
}
