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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.1.40
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Index\Magento\Cms;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;

class Page extends AbstractIndex
{
    /**
     * @var PageHelper
     */
    private $pageHelper;

    public function __construct(
        PageHelper $pageHelper
    ) {
        $this->pageHelper = $pageHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Cms\Model\Page $page */
        foreach ($this->getCollection() as $page) {
            $items[] = [
                'title' => $page->getTitle(),
                'url'   => $this->pageHelper->getPageUrl($page->getIdentifier()),
            ];
        }

        return $items;
    }
}
