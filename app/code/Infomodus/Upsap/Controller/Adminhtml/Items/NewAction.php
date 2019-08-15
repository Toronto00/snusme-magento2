<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Upsap\Controller\Adminhtml\Items;

class NewAction extends \Infomodus\Upsap\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
