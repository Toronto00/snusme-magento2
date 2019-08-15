<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Items;

class MassDelete extends \Infomodus\Upsap\Controller\Adminhtml\Items
{
    public function execute()
    {
        $methodsIds = $this->getRequest()->getParam('upsapshippingmethod_id');
        if (!is_array($methodsIds)) {
            $this->messageManager->addError(__('Please select one or more methods.'));
        } else {
            try {
                foreach ($methodsIds as $methodId) {
                    $modelMethod = $this->_objectManager->create(
                        '\Infomodus\Upsap\Model\Items'
                    )->load(
                        $methodId
                    );
                    $modelMethod->delete();
                }
                $this->messageManager->addSuccess(__('Total of %1 record(s) were deleted.', count($methodsIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
