<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Upsap\Controller\Index;

class GetSessionAddressAP extends \Infomodus\Upsap\Controller\Index
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
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
