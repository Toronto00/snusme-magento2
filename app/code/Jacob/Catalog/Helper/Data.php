<?php

namespace Jacob\Catalog\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    public function __construct(
        \Magefan\GeoIp\Model\IpToCountryRepository $ipToCountryRepository,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->ipToCountryRepository = $ipToCountryRepository;
        $this->remoteAddress         = $remoteAddress->getRemoteAddress();
        $this->countryFactory        = $countryFactory;
    }

    public function getIpLocation()
    {
        if ($this->_isDevEnv()) {
            $devCountryCode = $this->ipToCountryRepository->getCountryCode('171.7.236.152');
            $country        = $this->countryFactory->create()->loadByCode($devCountryCode);

            return $country->getName();
        }

        $visitorCountyCode  = $this->ipToCountryRepository->getVisitorCountryCode();
        $country            = $this->countryFactory->create()->loadByCode($visitorCountyCode);
        
        return $country->getName();
    }

    protected function _isDevEnv()
    {
        return $this->remoteAddress == '172.17.0.1';
    }
}