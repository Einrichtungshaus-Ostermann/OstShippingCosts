<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Shipping Costs
 *
 * @package   OstShippingCosts
 *
 * @author    Eike Brandt-Warneke <e.brandt-warneke@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstShippingCosts\Commands;

use Enlight_Components_Db_Adapter_Pdo_Mysql as Db;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncSameDayDeliveryZipCommand extends ShopwareCommand
{
    /**
     * ...
     *
     * @var Db
     */
    private $db;

    /**
     * @param Db  $db
     */
    public function __construct(Db $db)
    {
        parent::__construct();
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
        $output->writeln('not yet implemented');
    }
}
