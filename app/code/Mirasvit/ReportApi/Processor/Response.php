<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report
 * @version   1.3.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Processor;

use Magento\Framework\Api\AbstractSimpleObject;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;

class Response extends AbstractSimpleObject implements ResponseInterface
{
    const ITEMS = 'items';
    const REQUEST = 'request';
    const TOTALS = 'totals';
    const SIZE = 'size';
    const COLUMNS = 'columns';

    public function getColumns()
    {
        return $this->_get(self::COLUMNS);
    }

    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    public function getRequest()
    {
        return $this->_get(self::REQUEST);
    }

    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }

    public function getSize()
    {
        return $this->_get(self::SIZE);
    }

    public function toArray()
    {
        $columns = [];
        foreach ($this->getColumns() as $column) {
            $columns[] = $column->toArray();
        }

        $items = [];
        foreach ($this->getItems() as $item) {
            $items[] = [
                ResponseItem::DATA           => $item->getData(),
                ResponseItem::FORMATTED_DATA => $item->getFormattedData(),
            ];
        }

        return [
            self::REQUEST => $this->getRequest()->toArray(),
            self::SIZE    => $this->getSize(),
            self::COLUMNS => $columns,
            self::TOTALS  => [
                ResponseItem::DATA           => $this->getTotals()->getData(),
                ResponseItem::FORMATTED_DATA => $this->getTotals()->getFormattedData(),
            ],
            self::ITEMS   => $items,
        ];
    }
}