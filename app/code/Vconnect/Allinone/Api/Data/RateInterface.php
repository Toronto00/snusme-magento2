<?php

namespace Vconnect\Allinone\Api\Data;

/**
 * Allinone Rate interface.
 * @api
 */
interface RateInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                                            = 'vconnect_allinone_quote_shipping_rate_id';
    const VCONNECT_ALLINONE_QUOTE_SHIPPING_RATE_ID      = 'vconnect_allinone_quote_shipping_rate_id';
    const QUOTE_ID                                      = 'quote_id';
    const ADDRESS_ID                                    = 'address_id';
    const RATE_DATA                                     = 'rate_data';
    const DATE_CREATED                                  = 'date_created';

    /**#@-*/

    /**
     * Get ID
     *
     * @return int
     */
    public function getId();

    /**
     * Get Rate ID
     *
     * @return int
     */
    public function getRateId();

    /**
     * Get Quote ID
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Get Address ID
     *
     * @return int
     */
    public function getAddressId();

    /**
     * Get rate data
     *
     * @return string
     */
    public function getRateData();

    /**
     * Get date created
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getDateCreated();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setId($id);

    /**
     * Set Vconnect Allinone Quote Shipping Rate ID
     *
     * @param int $vconnectAllinoneQuoteShippingRateId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setRateId($vconnectAllinoneQuoteShippingRateId);

    /**
     * Set Quote ID
     *
     * @param int $quoteId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Set Rate ID
     *
     * @param int $addressId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setAddressId($addressId);

    /**
     * Set rate data
     *
     * @param string $rateData
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setRateData($rateData);

    /**
     * Set date created
     *
     * @param string $dateCreated
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setDateCreated($dateCreated);
}