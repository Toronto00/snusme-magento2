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



namespace Mirasvit\Search\Service;

use Mirasvit\Core\Service\AbstractValidator;
use Magento\Framework\Module\Manager;

class ValidationService extends AbstractValidator
{
    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function testMageworksSearchSuiteConflict()
    {
        if ($this->moduleManager->isEnabled('Mageworks_SearchSuite')) {
            return [self::FAILED, __FUNCTION__, 'Please disable or delete Mageworks_SearchSuite module'];
        } else {
            return [self::SUCCESS, __FUNCTION__, []];
        }
    }

    // public function testManadev_ProductCollectionConflict()
    // {
    //     if ($this->moduleManager->isEnabled('Manadev_ProductCollection')) {
    //         return [self::FAILED, __FUNCTION__, 'Please disable or delete Manadev_ProductCollection module'];
    //     } else {
    //         return [self::SUCCESS, __FUNCTION__, []];
    //     }
    // }

    // public function testManadev_LayeredNavigationConflict()
    // {
    //     if ($this->moduleManager->isEnabled('Manadev_LayeredNavigation')) {
    //         return [self::FAILED, __FUNCTION__, 'Please disable or delete Manadev_LayeredNavigation module'];
    //     } else {
    //         return [self::SUCCESS, __FUNCTION__, []];
    //     }
    // }

    public function testMagento_SolrConflict()
    {
        if ($this->moduleManager->isEnabled('Magento_Solr')) {
            return [self::FAILED, __FUNCTION__, 'If you use Magento Cloud please disable Magento_Solr module'];
        } else {
            return [self::SUCCESS, __FUNCTION__, []];
        }
    }

    public function testMagento_ElasticSearchConflict()
    {
        if ($this->moduleManager->isEnabled('Magento_ElasticSearch')) {
            return [self::FAILED, __FUNCTION__, 'If you use Magento Cloud please disable Magento_ElasticSearch module'];
        } else {
            return [self::SUCCESS, __FUNCTION__, []];
        }
    }
}
