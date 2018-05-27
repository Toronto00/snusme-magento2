<?php

namespace Vconnect\Allinone\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Vconnect\Allinone\Api\Data\RateSearchResultsInterfaceFactory;
use Vconnect\Allinone\Api\Data\RateInterface;
use Vconnect\Allinone\Api\Data\RateInterfaceFactory;
use Vconnect\Allinone\Api\RateRepositoryInterface;
use Vconnect\Allinone\Model\ResourceModel\Rate as RateResource;
use Vconnect\Allinone\Model\ResourceModel\Rate\CollectionFactory;

class RateRepository implements RateRepositoryInterface
{
    /**
     * @var RateResource
     */
    private $rateResource;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var RateSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var RateInterface
     */
    private $rateInterface;

    /**
     * @var RateInterfaceFactory
     */
    private $rateInterfaceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * RateRepository constructor.
     * @param RateResource $rateResource
     * @param RateFactory $rateFactory
     * @param CollectionFactory $collectionFactory
     * @param RateSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param RateInterfaceFactory $rateInterfaceFactory
     */
    public function __construct(
        RateResource $rateResource,
        RateFactory $rateFactory,
        CollectionFactory $collectionFactory,
        RateSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        RateInterface $rateInterface,
        RateInterfaceFactory $rateInterfaceFactory
    ) {
        $this->rateResource = $rateResource;
        $this->rateFactory = $rateFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->rateInterface = $rateInterface;
        $this->rateInterfaceFactory = $rateInterfaceFactory;
    }

    /**
     * Save rate data
     *
     * @param RateInterface $rate
     * @return int
     * @throws CouldNotSaveException
     */
    public function save(RateInterface $rate)
    {
        try {
            $this->rateResource->save($rate);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Allinone rate: %1',
                $exception->getMessage()
            ));
        }

        return $rate->getId();
    }

    /**
     * Get Allinone rate by ID
     *
     * @param int $rateId
     * @return Rate
     * @throws NoSuchEntityException
     */
    public function getById($rateId)
    {
        $rate = $this->rateFactory->create();

        $this->rateResource->load($rate, $rateId, 'vconnect_allinone_quote_shipping_rate_id');

        if (!$rate->getId()) {
            throw new NoSuchEntityException(__('Allinone rate with id "%1" does not exist.', $rateId));
        }

        return $rate;
    }

    /**
     * Load Allinone rate data collection by given search criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Vconnect\Allinone\Api\Data\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        $collection = $this->collectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortRates = $searchCriteria->getSortRates();
        if ($sortRates) {
            /** @var SortRate $sortRate */
            foreach ($sortRates as $sortRate) {
                $collection->addRate(
                    $sortRate->getField(),
                    ($sortRate->getDirection() == SortRate::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $rates = [];
        /** @var Rate $rateModel */
        foreach ($collection as $rateModel) {
            $rateData = $this->rateInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $rateData,
                $rateModel->getData(),
                'Vconnect\Allinone\Api\Data\RateInterface'
            );
            $rates[] = $rateData;
        }
        $searchResults->setItems($rates);
        return $searchResults;
    }

    /**
     * Delete rate
     *
     * @param RateInterface $rate
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RateInterface $rate)
    {
        try {
            $this->rateResource->delete($rate);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the allinone rate: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete Allinone rate by given ID
     *
     * @param int $rateId
     * @return bool
     */
    public function deleteById($rateId)
    {
        return $this->delete($this->getById($rateId));
    }
}
