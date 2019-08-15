<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml\Items;

class Edit extends \Infomodus\Caship\Controller\Adminhtml\Items
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->items->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('infomodus_caship/*');
                return;
            }

            if ($model->getUserGroupIds() != '') {
                $model->setUserGroupIds(trim($model->getUserGroupIds(), ','));
            }

            if ($model->getStoreId() != '') {
                $model->setStoreId(trim($model->getStoreId(), ','));
            }
        } else {
            $negotiatedRates = 0;
            if($this->_conf->isModuleOutputEnabled("Infomodus_Upslabel")){
                $negotiatedRates = $this->_conf->getStoreConfig('upslabel/ratepayment/negotiatedratesindicator');
            }
            $model->setNegotiated($negotiatedRates);
        }
        // set entered data if was error when we do save
        $data = $this->session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_infomodus_caship_items', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_items_edit');
        $this->_view->renderLayout();
    }
}
