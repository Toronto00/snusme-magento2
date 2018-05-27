<?php

namespace Vconnect\Allinone\Model;

use Magento\Framework\Model\AbstractModel;
use Vconnect\Allinone\Api\Data\RateInterface;

class Rate extends AbstractModel implements RateInterface
{
    protected function _construct()
    {
        $this->_init('Vconnect\Allinone\Model\ResourceModel\Rate');
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Get Vconnect Allinone Quote Shipping Rate ID
     *
     * @return int
     */
    public function getRateId()
    {
        return $this->getData(self::VCONNECT_ALLINONE_QUOTE_SHIPPING_RATE_ID);
    }

    /**
     * Get Quote ID
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Get Address ID
     *
     * @return int
     */
    public function getAddressId()
    {
        return $this->getData(self::ADDRESS_ID);
    }

    /**
     * Get Rate data
     *
     * @return string
     */
    public function getRateData()
    {
        return $this->getData(self::RATE_DATA);
    }

    /**
     * Get date created
     *
     * @return \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public function getDateCreated()
    {
        return $this->getData(self::DATE_CREATED);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Set Vconnect Allinone Quote Shipping Rate ID
     *
     * @param int $vconnectAllinoneQuoteShippingRateId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setRateId($vconnectAllinoneQuoteShippingRateId)
    {
        return $this->setData(self::VCONNECT_ALLINONE_QUOTE_SHIPPING_RATE_ID, $vconnectAllinoneQuoteShippingRateId);
    }

    /**
     * Set Quote ID
     *
     * @param int $quoteId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Set Address ID
     *
     * @param int $addressId
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setAddressId($addressId)
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * Set Rate data
     *
     * @param string $rateData
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setRateData($rateData)
    {
        return $this->setData(self::RATE_DATA, $rateData);
    }

    /**
     * Set date created
     *
     * @param string $date
     * @return \Vconnect\Allinone\Api\Data\RateInterface
     */
    public function setDateCreated($date)
    {
        return $this->setData(self::DATE_CREATED, $date);
    }
}