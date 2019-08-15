<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Items;

class Save extends \Infomodus\Upsap\Controller\Adminhtml\Items
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $model = $this->_objectManager->create('Infomodus\Upsap\Model\Items');
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                if(count($data['country_ids']) > 0) {
                    $data['country_ids'] = implode(',', $data['country_ids']);
                }

                if (isset($data['user_group_ids'])) {
                    $data['user_group_ids'] = ','.implode(',', $data['user_group_ids']).',';
                }

                if (isset($data['store_id'])) {
                    $data['store_id'] = implode(',', $data['store_id']);
                }

                if (isset($data['negotiated_amount_from'])) {
                    $data['negotiated_amount_from'] = str_replace(",", ".", $data['negotiated_amount_from']);
                }

                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong item is specified.'));
                    }
                }

                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();
                $this->messageManager->addSuccess(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('infomodus_upsap/*/edit', ['id' => $model->getId()]);
                    return;
                }

                $this->_redirect('infomodus_upsap/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('infomodus_upsap/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('infomodus_upsap/*/new');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('infomodus_upsap/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }

        $this->_redirect('infomodus_upsap/*/');
    }
}
