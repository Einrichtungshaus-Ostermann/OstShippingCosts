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
use OstErpApi\Api\Api;
use OstErpApi\Struct\Zip;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncSelfDeliveryZipCommand extends ShopwareCommand
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
        // are we inhouse and do we have the erp api!?
        if (!Shopware()->Container()->initialized('ost_erp_api.api')) {
            $output->writeln('ost-erp-api not avaiable');
            return;
        }

        /* @var $api Api */
        $api = Shopware()->Container()->get('ost_erp_api.api');

        // get every zip with from and to
        /* @var $erpZips Zip[] */
        $erpZips = $api->findBy(
            'zip',
            [
                "L2LFB1 != '999' AND L2PLZG != '999'"
            ]
        );

        // remove every current zip
        $query = '
            TRUNCATE ost_shipping_costs_selfdelivery;
        ';
        $this->db->query($query);

        // start the progress bar
        $progressBar = new ProgressBar($output, count($erpZips));
        $progressBar->setRedrawFrequency(10);
        $progressBar->start();

        // ...
        foreach ($erpZips as $erpZip) {
            // advance progress bar
            $progressBar->advance();

            // set start and end zips and check to prevent massive failure
            $start = (int) $erpZip->getRangeFrom();
            $end = (int) $erpZip->getRangeTo();

            // max 100 or this counts as invalid
            if (($end - $start > 100) || ($end - $start < 0)) {
                // next
                continue;
            }

            // every zip
            for ($i = $start; $i <= $end; ++$i) {
                // ...
                $query = '
                    INSERT INTO ost_shipping_costs_selfdelivery
                    SET zip = :zip
                ';
                $this->db->query($query, ['zip' => $i]);
            }
        }

        // done
        $progressBar->finish();
        $output->writeln('');
    }
}
