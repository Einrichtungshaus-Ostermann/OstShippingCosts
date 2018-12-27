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

namespace OstShippingCosts\Setup;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

class Install
{
    /**
     * ...
     *
     * @var array
     */
    public static $attributes = [
        's_premium_dispatch_attributes' => [
            [
                'column' => 'ost_shipping_costs_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Automatik aktivieren',
                    'helpText'         => 'Generiert automatisch die Versandkosten für diese Versandart.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 100
                ]
            ],
            [
                'column' => 'ost_shipping_costs_type',
                'type'   => 'combobox',
                'data'   => [
                    'label'            => 'Art des Versands',
                    'helpText'         => 'Welchem Typ entspricht diese Versandart?',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 105,
                    'arrayStore'       => [
                        ['key' => '1', 'value' => 'Paket'],
                        ['key' => '2', 'value' => 'Spedition']
                    ],
                ]
            ]
        ]
    ];
    /**
     * Main bootstrap object.
     *
     * @var Plugin
     */
    protected $plugin;

    /**
     * ...
     *
     * @var InstallContext
     */
    protected $context;

    /**
     * ...
     *
     * @var ModelManager
     */
    protected $modelManager;

    /**
     * ...
     *
     * @var CrudService
     */
    protected $crudService;

    /**
     * ...
     *
     * @param Plugin         $plugin
     * @param InstallContext $context
     * @param ModelManager   $modelManager
     * @param CrudService    $crudService
     */
    public function __construct(Plugin $plugin, InstallContext $context, ModelManager $modelManager, CrudService $crudService)
    {
        // set params
        $this->plugin = $plugin;
        $this->context = $context;
        $this->modelManager = $modelManager;
        $this->crudService = $crudService;
    }

    /**
     * ...
     *
     * @throws \Exception
     */
    public function install()
    {
    }
}
