<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml\Items;

use Infomodus\Caship\Controller\Adminhtml\Items;

class Save extends Items
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->items->create();
                $data = $this->getRequest()->getPostValue();
                if (isset($data['country_ids'])) {
                    $data['country_ids'] = implode(',', $data['country_ids']);
                }

                if (isset($data['user_group_ids'])) {
                    $data['user_group_ids'] = ','.implode(',', $data['user_group_ids']).',';
                }

                if (isset($data['store_id'])) {
                    $data['store_id'] = implode(',', $data['store_id']);
                }

                if (isset($data['dinamic_price']) && $data['dinamic_price'] == 1 && isset($data['company_type_all'])) {
                    $data['company_type'] = $data['company_type_all'];
                    switch ($data['company_type_all']) {
                        case 'ups':
                        case 'upsinfomodus':
                            $data['upsmethod_id'] = $data['upsmethod_id_all'];
                            break;
                        case 'dhl':
                        case 'dhlinfomodus':
                            $data['dhlmethod_id'] = $data['dhlmethod_id_all'];
                            break;
                        case 'fedex':
                        case 'fedexinfomodus':
                            $data['fedexmethod_id'] = $data['fedexmethod_id_all'];
                            break;
                    }
                }
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }
                $model->setData($data);
                $this->session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $this->session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('infomodus_caship/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('infomodus_caship/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('infomodus_caship/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('infomodus_caship/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->loggerInterface->critical($e);
                $this->session->setPageData($data);
                $this->_redirect('infomodus_caship/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->_redirect('infomodus_caship/*/');
    }
}
