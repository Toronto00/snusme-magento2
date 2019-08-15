<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Points;

class Edit extends \Infomodus\Upsap\Controller\Adminhtml\Points
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Infomodus\Upsap\Model\Points');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                $this->_redirect('infomodus_upsap/*');
                return;
            }
        }
        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $this->_coreRegistry->register('current_infomodus_upsap_points', $model);
        $this->_initAction();
        $this->_view->getLayout()->getBlock('items_points_edit');
        $this->_view->renderLayout();
    }
}
