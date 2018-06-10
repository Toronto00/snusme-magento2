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
 * @package   mirasvit/module-search
 * @version   1.0.76
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


namespace Mirasvit\Search\Api\Service;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

interface ScoreServiceInterface
{
    const SOLD_QTY = 'sold_qty';
    const PROPERTY_NAME = 'relevance_by_sales';
    
    /**
     * @param Select $select
     * @param ResourceConnection $resource
     * @param Table $table
     * @return mutate $select
     */
    public function modifyQuery(Select $select, ResourceConnection $resource, Table $table);
}
