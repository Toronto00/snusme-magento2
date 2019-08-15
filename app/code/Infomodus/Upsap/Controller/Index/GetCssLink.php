<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Infomodus\Upsap\Controller\Index;

class GetCssLink extends \Infomodus\Upsap\Controller\Index
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $result = $this->_resultJsonFactory->create();
        /*multistore*/
        $storeId = $this->_storeManage->getStore()->getId();
        /*multistore*/
        return $result->setData(["link" => $this->_conf->getStoreConfig('carriers/upsap/css', $storeId), 'edit' => $this->_conf->getStoreConfig('carriers/upsap/search_ui', $storeId)==0?'false':'true']);
    }
}
