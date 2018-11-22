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
     * @var array
     */
    protected $attributes = [
        's_premium_dispatch_attributes' => [
            [
                'column' => 'ost_shipping_costs_status',
                'type'   => 'boolean',
                'data'   => [
                    'label'            => 'Automatik aktivieren',
                    'helpText'         => 'Generiert automatisch den Namen der Versandart sowie die Versandkosten für diese Versandart.',
                    'translatable'     => false,
                    'displayInBackend' => true,
                    'custom'           => false
                ]
            ]
        ]
    ];

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
        // ...
        foreach ($this->attributes as $table => $attributes) {
            foreach ($attributes as $attribute) {
                $this->crudService->update(
                    $table,
                    $attribute['column'],
                    $attribute['type'],
                    $attribute['data']
                );
            }
        }

        // ...
        $this->modelManager->generateAttributeModels(array_keys($this->attributes));
    }
}
