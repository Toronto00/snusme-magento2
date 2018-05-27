<?php
/* 
 * The MIT License
 *
 * Copyright 2016 vConnect.dk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @category Magento
 * @package Vconnect_AllInOne
 * @author vConnect
 * @email kontakt@vconnect.dk
 * @class Vconnect_AllInOne_Helper_Data
 */

namespace Vconnect\Allinone\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * InstallData constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $storeCountryId = $this->getStoreConfig('shipping/origin/country_id');
        if ($storeCountryId == 'SE') {
            $this->setStoreConfig('carriers/vconnectpostnord/button_label', 'Klicka här för att välja leveransalternativ');
            $this->setStoreConfig('carriers/vconnectpostnord/button_label_active', 'Klicka här för att välja annat leveransalternativ');
            $this->setStoreConfig('carriers/vconnectpostnord/title', 'Leverans med PostNord');

            $this->setStoreConfig('carriers/vconnect_postnord_business/name', 'Till företag');
            $this->setStoreConfig('carriers/vconnect_postnord_business/arrival_option_text', '08:00 - 16:00');
            $this->setStoreConfig('carriers/vconnect_postnord_business/transit_time', '1-3 dagar');
            
            $this->setStoreConfig('carriers/vconnect_postnord_home/name', 'Till dörren');
            $this->setStoreConfig('carriers/vconnect_postnord_home/arrival_option_text', '08:00 - 17:00');
            $this->setStoreConfig('carriers/vconnect_postnord_home/transit_time', '1-3 dagar');
            $this->setStoreConfig('carriers/vconnect_postnord_home/additional_fee_label', '17:00 - 21:00');

            $this->setStoreConfig('carriers/vconnect_postnord_pickup/name', 'Till serviceställe');
            $this->setStoreConfig('carriers/vconnect_postnord_pickup/transit_time', '1-3 dagar');

            $this->setStoreConfig('carriers/vconnect_postnord_mailbox/name', 'Till postlådan');
            $this->setStoreConfig('carriers/vconnect_postnord_mailbox/arrival_option_text', '2-3 dagar');
            $this->setStoreConfig('carriers/vconnect_postnord_mailbox/transit_time', '1-3 dagar');
            $this->setStoreConfig('carriers/vconnect_postnord_mailbox/additional_fee_label', '1 dag');

            $this->setStoreConfig('carriers/vconnect_postnord_eu/name', 'EU');
            $this->setStoreConfig('carriers/vconnect_postnord_intl/name', 'To door');

        } elseif ($storeCountryId == 'FI') {
            $this->setStoreConfig('carriers/vconnectpostnord/button_label', 'Valitse toimitustapa');
            $this->setStoreConfig('carriers/vconnectpostnord/button_label_active', 'Valitse toinen toimitustapa');
            $this->setStoreConfig('carriers/vconnectpostnord/title', 'PostNord');

            $this->setStoreConfig('carriers/vconnect_postnord_business/name', 'Toimitus työpaikalle');
            $this->setStoreConfig('carriers/vconnect_postnord_business/arrival_option_text', '1-3 arkipäivää. Toimitus klo 8-17 välisenä aikana.');
            $this->setStoreConfig('carriers/vconnect_postnord_business/transit_time', '1-3 päivää');
            
            $this->setStoreConfig('carriers/vconnect_postnord_home/name', 'Toimitus kotiin');
            $this->setStoreConfig('carriers/vconnect_postnord_home/arrival_option_text', '1-3 arkipäivää. Toimitus klo 8-17 välisenä aikana.');
            $this->setStoreConfig('carriers/vconnect_postnord_home/transit_time', '1-3 päivää');

            $this->setStoreConfig('carriers/vconnect_postnord_pickup/name', 'Toimitus palvelupisteeseen');
            $this->setStoreConfig('carriers/vconnect_postnord_pickup/transit_time', '1-4 päivää');

            $this->setStoreConfig('carriers/vconnect_postnord_eu/name', 'EU');
            $this->setStoreConfig('carriers/vconnect_postnord_intl/name', 'International');
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function getStoreConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $path
     * @return mixed
     */
    protected function setStoreConfig($path, $value)
    {
        $this->resourceConfig->saveConfig(
            $path,
            $value,
            'default',
            0
        );
    }
}
