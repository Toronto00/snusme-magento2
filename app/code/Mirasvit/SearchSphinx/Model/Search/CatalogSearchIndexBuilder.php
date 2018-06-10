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
 * @package   mirasvit/module-search-sphinx
 * @version   1.1.25
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchSphinx\Model\Search;

use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\StrategyMapper as BaseSelectStrategyMapper;
use Magento\CatalogSearch\Model\Search\FilterMapper\DimensionsProcessor;
use Magento\CatalogSearch\Model\Search\FilterMapper\FilterMapper;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerBuilder;
use Magento\CatalogSearch\Model\Search\TableMapper;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchSphinx\Adapter\MapperQL;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\BaseSelectStrategyInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class CatalogSearchIndexBuilder extends \Magento\CatalogSearch\Model\Search\IndexBuilder
{
    /**
     * @var MapperQL
     */
    private $mapperQL;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DimensionsProcessor
     */
    private $dimensionsProcessor;

    /**
     * @var SelectContainerBuilder
     */
    private $selectContainerBuilder;

    /**
     * @var BaseSelectStrategyMapper
     */
    private $baseSelectStrategyMapper;

    /**
     * @var FilterMapper
     */
    private $filterMapper;

    public function __construct(
        MapperQL $mapperQL,
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver,
        TableMapper $tableMapper,
        ScopeResolverInterface $dimensionScopeResolver,
        $dimensionsProcessor = null,
        $selectContainerBuilder = null,
        $baseSelectStrategyMapper = null,
        $filterMapper = null
    ) {
        $this->mapperQL = $mapperQL;
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->storeManager = $storeManager;

        $this->dimensionsProcessor = $dimensionsProcessor ?: ObjectManager::getInstance()
            ->get(DimensionsProcessor::class);

        $this->selectContainerBuilder = $selectContainerBuilder ?: ObjectManager::getInstance()
            ->get(SelectContainerBuilder::class);

        $this->baseSelectStrategyMapper = $baseSelectStrategyMapper ?: ObjectManager::getInstance()
            ->get(BaseSelectStrategyMapper::class);

        $this->filterMapper = $filterMapper ?: ObjectManager::getInstance()
            ->get(FilterMapper::class);

        parent::__construct($resource, $config, $storeManager, $conditionManager, $scopeResolver, $tableMapper, $dimensionScopeResolver, $dimensionsProcessor, $selectContainerBuilder, $baseSelectStrategyMapper, $filterMapper);
    }

    /**
     * {@inheritdoc}
     */
    public function build(RequestInterface $request)
    {
        /** @var SelectContainer $selectContainer */
        $selectContainer = $this->selectContainerBuilder->buildByRequest($request);
        /** @var BaseSelectStrategyInterface $baseSelectStrategy */
        $baseSelectStrategy = $this->baseSelectStrategyMapper->mapSelectContainerToStrategy($selectContainer);

        $selectContainer = $this->createBaseSelect($selectContainer, $request);
        $selectContainer = $this->filterMapper->applyFilters($selectContainer);

        $selectContainer = $this->dimensionsProcessor->processDimensions($selectContainer);

        return $selectContainer->getSelect();
    }

    public function createBaseSelect(SelectContainer $selectContainer, RequestInterface $request)
    {
        $select = $this->resource->getConnection()->select();
        $mainTableAlias = $selectContainer->isFullTextSearchRequired() ? 'eav_index' : 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        //        if ($selectContainer->isFullTextSearchRequired()) {
        $table = $this->mapperQL->buildQuery($request);

        $select->joinInner(
            ['search_index' => $table->getName()],
            'eav_index.entity_id = search_index.entity_id',
            ['score']
        );
        //        }

        $selectContainer = $selectContainer->updateSelect($select);
        return $selectContainer;
    }
}
