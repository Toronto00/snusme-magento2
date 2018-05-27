<?php

namespace Vconnect\Allinone\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface RateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Vconnect\Allinone\Api\Data\RateInterface[]
     */
    public function getItems();

    /**
     * @param \Vconnect\Allinone\Api\Data\RateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}