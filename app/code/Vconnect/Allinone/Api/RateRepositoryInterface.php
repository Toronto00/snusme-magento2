<?php

namespace Vconnect\Allinone\Api;

/**
 * Rate CRUD interface.
 * @api
 */
interface RateRepositoryInterface
{
    /**
     * Save rate.
     *
     * @param \Vconnect\Allinone\Api\Data\RateInterface $rate
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function save(\Vconnect\Allinone\Api\Data\RateInterface $rate);

    /**
     * Retrieve rate.
     *
     * @param int $rateId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($rateId);

    /**
     * Retrieve ratees matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Vconnect\Allinone\Api\Data\RateSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete rate.
     *
     * @param \Vconnect\Allinone\Api\Data\RateInterface $rate
     * @return bool true on success
     */
    public function delete(\Vconnect\Allinone\Api\Data\RateInterface $rate);

    /**
     * Delete rate by ID.
     *
     * @param int $rateId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById($rateId);
}