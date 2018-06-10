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



namespace Mirasvit\Search\Service\QueryScore;

use Mirasvit\Search\Api\Service\ScoreServiceInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;

class DefaultScore implements ScoreServiceInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function modifyQuery(Select $select, ResourceConnection $resource, Table $table)
    {   
        return $select->from(
                ['main_table' => $resource->getTableName('catalog_product_entity')],
                ['entity_id', 'search_weight']
            )->joinLeft(
                ['score' => $table->getName()],
                 'score.entity_id = main_table.entity_id', []
            )->where('(main_table.search_weight > 0 or main_table.search_weight < 0)');
    }
}