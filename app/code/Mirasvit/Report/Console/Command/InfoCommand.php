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



namespace Mirasvit\Report\Console\Command;

use Magento\Framework\App\State;
use Mirasvit\ReportApi\Api\SchemaInterface;
use Mirasvit\ReportApi\Service\SelectServiceFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command
{
    private $provider;

    private $selectServiceFactory;

    /**
     * @var State
     */
    private $state;

    public function __construct(
        State $state,
        SchemaInterface $provider,
        SelectServiceFactory $selectServiceFactory
    ) {
        $this->state = $state;
        $this->provider = $provider;
        $this->selectServiceFactory = $selectServiceFactory;

        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mirasvit:report:info')
            ->setDescription('Returns current schema')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $selectService = $this->selectServiceFactory->create();
        $this->provider->getTables();

        foreach ($this->provider->getRelations() as $idx => $relation) {
            $output->writeln("<info>Rel #$idx:</info> $relation");
        }

        $output->writeln("\n\n\n\n");

        foreach ($this->provider->getTables() as $leftTable) {
            foreach ($this->provider->getTables() as $rightTable) {
                if ($leftTable === $rightTable) {
                    continue;
                }

                $output->writeln("<info>$leftTable to $rightTable</info>");

                $relations = $selectService->joinWay($leftTable, $rightTable);
                $tbl = $leftTable;
                foreach ($relations as $idx => $relation) {
                    $output->writeln("\t$idx\t $tbl to " . $relation->getOppositeTable($tbl));
                    $tbl = $relation->getOppositeTable($tbl);
                }
            }
        }

        //        die();

        $output->writeln("\n\n\n\n");

        foreach ($this->provider->getTables() as $leftTable) {
            foreach ($this->provider->getTables() as $rightTable) {
                $output->write("$leftTable to $rightTable related as ");
                $type = $selectService->getRelationType($leftTable, $rightTable);
                $output->writeln("<info>$type</info>");
            }
        }
    }
}
