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

use OstShippingCosts\Models;
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
            ],
            [
                'column' => 'ost_shipping_costs_surcharge',
                'type'   => 'integer',
                'data'   => [
                    'label'            => 'Aufschlag',
                    'helpText'         => 'Ein genereller, einmaliger Aufschlag (Brutto / Euro) zu den berechneten Versandkosten. Der Aufschlag kann z.B. für Versandarten für Lieferungen ins Ausland genutzt werden.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 107
                ]
            ],
            [
                'column' => 'ost_shipping_costs_selfdelivery_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Eigenauslieferung aktivieren',
                    'helpText'         => 'Ist diese Versandart als Eigenauslieferung definiert? Nur möglich, wenn die Art des Versands "Spedition" ist. Diese Versandart wird aktiviert, sobald sich die Auslieferung in bekannten PLZ-Gebieten befindet. Sollte es sich bei dieser Versandart nicht um eine Eigenauslieferung handelt, dann wird diese Versandart in bekannten PLZ-Gebieten deaktiviert.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 110
                ]
            ],
            [
                'column' => 'ost_shipping_costs_drop_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Abwurfaufträge abfertigen',
                    'helpText'         => 'Soll diese Versandart Abwurfaufträge abfertigen? Nur möglich, wenn die Art des Versands "Spedition" ist. Andere "Spedition" Versandarten werden deaktiviert.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 115
                ]
            ],
            [
                'column' => 'ost_shipping_costs_express_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Express Versandart',
                    'helpText'         => 'Ist dies eine Express Versandart - z.B. DHL Express?',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 120
                ]
            ],
            [
                'column' => 'ost_shipping_costs_samedaydelivery_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Same-Day Versandart',
                    'helpText'         => 'Ist dies eine Same-Day-Delivery Versandart - z.B. DHL Same Day Delivery? Nur möglich, wenn die Art des Versands "Paket" ist.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false,
                    'position'         => 125
                ]
            ],
        ]
    ];

    /**
     * ...
     *
     * @var array
     */
    public static $models = [
        Models\SelfDelivery::class,
        Models\SameDayDelivery::class
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
