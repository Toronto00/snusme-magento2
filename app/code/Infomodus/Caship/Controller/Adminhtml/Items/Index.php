<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml\Items;

class Index extends \Infomodus\Caship\Controller\Adminhtml\Items
{
    /**
     * Items list.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Infomodus_Caship::caship');
        $resultPage->getConfig()->getTitle()->prepend(__('Infomodus Items'));
        $resultPage->addBreadcrumb(__('Infomodus'), __('Infomodus'));
        $resultPage->addBreadcrumb(__('Items'), __('Items'));
        return $resultPage;
    }
}
