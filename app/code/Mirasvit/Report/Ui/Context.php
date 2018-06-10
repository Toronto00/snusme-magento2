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
 * @package   mirasvit/module-report
 * @version   1.3.18
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Ui;

use Mirasvit\Report\Api\Data\ReportInterface;
use Mirasvit\Report\Model\Config;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Backend\Model\Session;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Context
{
    public function __construct(
        Config $config,
        ReportRepositoryInterface $reportRepository,
        Registry $registry,
        Session $session,
        ContextInterface $context
    ) {
        $this->config = $config;
        $this->reportRepository = $reportRepository;
        $this->registry = $registry;
        $this->session = $session;
        $this->context = $context;

    }

    /**
     * @return ReportInterface
     */
    public function getReport()
    {
        $report = $this->registry->registry('current_report');

        if (!$report) {
            $report = $this->reportRepository->get(
                $this->context->getRequestParam('report', 'order_overview'),
                $this->context->getRequestParam('id', null)
            );

            $this->registry->register('current_report', $report);
        }
        $report->setUiContext($this);

        return $report;
    }

    /**
     * @return string
     */
    public function getActiveDimension()
    {
        if ($dimension = $this->context->getRequestParam('dimension')) {
            return $dimension;
        }

        return $this->getReport()->getDefaultDimension();
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
