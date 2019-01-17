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

use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Enlight_Components_Db_Adapter_Pdo_Mysql as Db;
use OstErpApi\Api\Api;
use OstErpApi\Struct\Zip;

class SyncSelfDeliveryZipCommand extends ShopwareCommand
{
    /**
     * ...
     *
     * @var Db
     */
    private $db;

    /**
     * ...
     *
     * @var Api
     */
    private $api;

    /**
     * @param Db $db
     * @param Api $api
     */
    public function __construct(Db $db, Api $api)
    {
        parent::__construct();
        $this->db = $db;
        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get every zip with from and to
        /* @var $erpZips Zip[] */
        $erpZips = $this->api->findBy(
            'zip',
            [
                "L2LFB1 != '999' AND L2PLZG != '999'"
            ]
        );

        // remove every current zip
        $query = "
            TRUNCATE ost_shipping_costs_selfdelivery;
        ";
        $this->db->query( $query );

        // start the progress bar
        $progressBar = new ProgressBar($output, count($erpZips));
        $progressBar->setRedrawFrequency(10);
        $progressBar->start();

        // ...
        foreach ( $erpZips as $erpZip )
        {
            // advance progress bar
            $progressBar->advance();

            // set start and end zips and check to prevent massive failure
            $start = (int) $erpZip->getRangeFrom();
            $end   = (int) $erpZip->getRangeTo();

            // max 100 or this counts as invalid
            if ( ( $end - $start > 100 ) or ( $end - $start < 0 ) )
                // next
                continue;

            // every zip
            for ( $i = $start; $i++; $i <= $end )
            {
                // ...
                $query = "
                    INSERT INTO ost_shipping_costs_selfdelivery
                    SET zip = :zip
                ";
                $this->db->query( $query, array( 'zip' => $i ) );
            }
        }

        // done
        $progressBar->finish();
        $output->writeln('');
    }
}
