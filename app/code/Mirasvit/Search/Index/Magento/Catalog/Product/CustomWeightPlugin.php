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


namespace Mirasvit\Search\Index\Magento\Catalog\Product;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Mirasvit\Search\Api\Service\ScoreServiceInterface;

class CustomWeightPlugin
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ScoreServiceInterface
     */
    private $scoreService;

    public function __construct(
        ResourceConnection $resource,
        ScoreServiceInterface $scoreService
    ) {
        $this->resource = $resource;
        $this->scoreService = $scoreService;
    }

    /**
     * @param object $storage
     * @param Table  $table
     * @return Table
     */
    public function afterStoreApiDocuments($storage, Table $table)
    {
        $this->updateWeights($table);

        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($table->getName(), ['*'])
            ->order('score desc');

        return $storage->storeDocumentsFromSelect($select);
    }

    /**
     * @param Table $table
     * @return void
     */
    private function updateWeights(Table $table)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from($table->getName(), [new \Zend_Db_Expr('MAX(score)')]);

        $maxScore = $connection->fetchOne($select);

        if ($maxScore > 100) {
            $connection->update($table->getName(), ['score' => new \Zend_Db_Expr("score / $maxScore * 100")]);
        }
        $withWeight = $connection->select();

        $this->scoreService->modifyQuery($withWeight, $this->resource, $table);
      
        $withWeight = $connection->fetchAll($withWeight);

        foreach ($withWeight as $row) {
            $w = floatval($row['search_weight']);
            if ($w != 0 && $row['entity_id'] > 0) {
                $connection->update(
                    $table->getName(),
                    ['score' => new \Zend_Db_Expr("score + $w")],
                    'entity_id=' . $row['entity_id']
                );
            }
        }
    }
}
