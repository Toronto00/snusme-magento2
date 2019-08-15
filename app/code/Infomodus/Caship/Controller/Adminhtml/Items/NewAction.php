<?php
/**
 * Copyright © 2015 Infomodus. All rights reserved.
 */

namespace Infomodus\Caship\Controller\Adminhtml\Items;

class NewAction extends \Infomodus\Caship\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
