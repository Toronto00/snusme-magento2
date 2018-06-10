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
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Table;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;

use Magento\Catalog\Model\Product;

class SalesScore implements ScoreServiceInterface
{
    const SALES_SCORE_FACTOR = 0.5;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var Attribute
     */
    private $attribute;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    public function __construct(
        IndexRepositoryInterface $indexRepository,
        Attribute $attribute,
        StoreManagerInterface $storeManager,
        IndexScopeResolver $scopeResolver
    ) {
        $this->indexRepository = $indexRepository;
        $this->attribute = $attribute;
        $this->storeManager = $storeManager;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function modifyQuery(Select $select, ResourceConnection $resource, Table $table)
    {
        if (!$this->isActiveHandler()) { 
            return $select; 
        }
        $attributeId = $this->attribute->loadByCode(Product::ENTITY, ScoreServiceInterface::SOLD_QTY)->getId();

        $select->setPart(Select::WHERE, []);
        $columns = $select->getPart(Select::COLUMNS);
        foreach ($columns as $key => $column) {
            if ($columnKey = array_search('search_weight', $column)) {
                $searchWeight = new \Zend_Db_Expr('SUM(search_weight + POW(sold.data_index,'.
                self::SALES_SCORE_FACTOR .')) AS search_weight');
                $columns[$key][$columnKey] = $searchWeight;
            }
        }
        $select->setPart(Select::COLUMNS, $columns);

        return $select->joinLeft(
            ['sold'=> $this->getTableName()],
            'sold.entity_id = score.entity_id and sold.attribute_id='. $attributeId, []
            )->group('score.entity_id');
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function isActiveHandler()
    {
        return boolval($this->indexRepository->get('catalogsearch_fulltext')
            ->getProperty(ScoreServiceInterface::PROPERTY_NAME));
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getTableName()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeResolver->resolve('catalogsearch_fulltext', ["scope" => new Dimension('scope', $storeId)]);
    }
}