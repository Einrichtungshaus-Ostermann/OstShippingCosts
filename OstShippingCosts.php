<?php declare(strict_types=1);

/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Shipping Costs
 *
 * Calculates the shipping costs for every article and activates and/or deactivates
 * the dispatch method depending on the dispatch type (package or truck) saved
 * in the attribute.
 *
 * 1.0.0
 * - initial release
 *
 * 1.1.0
 * - added attribute to select dispatch type (package or truck). the name of the
 *   dispatch method is no longer rewritten by the plugin
 *
 * 1.1.1
 * - fixed attribute name for fullserviceprice
 * - changed source of shipping costs to always use calculated article shipping costs
 *
 * 1.1.2
 * - fixed default configuration value for attributeTag
 * - removed obsolete configuration value
 *
 * 1.2.0
 * - added self-delivery checkbox for dispatch methods for a specific area (via zip code)
 * - added drop checkbox for dispatch methods which can be configured to always
 *   be selected if the plugin configuration is set
 * - added express checkbox for dispatch methods with a specific surcharge
 *
 * 1.2.1
 * - fixed attribute description
 *
 * 1.2.2
 * - added command to sync zip codes for self-delivery via erp api
 *
 * 1.2.3
 * - changed base calculation of shipping costs from net to gross to fix faulty rounding
 *
 * 1.2.4
 * - fixed stock check for express shipping
 *
 * 1.2.5
 * - changed title of configuration
 *
 * 1.3.0
 * - added a general surcharge for calculated shipping costs which may be used for
 *   dispatch methods outside of germany
 *
 * 1.4.0
 * - split shipping costs listener into multiple services
 * - added calculation of addition articles
 *
 * 1.4.1
 * - moved attribute on the bottom of the configuration
 *
 * 1.4.2
 * - fixed addition calculation for inhouse package articles
 *
 * 1.4.3
 * - removed dependency injection from console command
 *
 * 1.4.4
 * - fixed article addition handling for moebel-shop
 *
 * 1.4.5
 * - fixed type casting of possible invalid parameter
 *
 * 1.4.6
 * - fixed missing container parameter for install
 *
 * 1.5.0
 * - added constants for for every shop in the foundation context configuration
 * - added same day delivery
 * - fixed handling of surcharge
 *
 * @package   OstShippingCosts
 *
 * @author    Eike Brandt-Warneke <e.brandt-warneke@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstShippingCosts;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OstShippingCosts extends Plugin
{
    /**
     * ...
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        // set plugin parameters
        $container->setParameter('ost_shipping_costs.plugin_dir', $this->getPath() . '/');
        $container->setParameter('ost_shipping_costs.view_dir', $this->getPath() . '/Resources/views/');

        // call parent builder
        parent::build($container);
    }

    /**
     * Activate the plugin.
     *
     * @param Context\ActivateContext $context
     */
    public function activate(Context\ActivateContext $context)
    {
        // clear complete cache after we activated the plugin
        $context->scheduleClearCache($context::CACHE_LIST_ALL);
    }

    /**
     * Install the plugin.
     *
     * @param Context\InstallContext $context
     *
     * @throws \Exception
     */
    public function install(Context\InstallContext $context)
    {
        // install the plugin
        $installer = new Setup\Install(
            $this,
            $context,
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );
        $installer->install();

        // update it to current version
        $updater = new Setup\Update(
            $this,
            $context,
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->getPath() . '/'
        );
        $updater->install();

        // call default installer
        parent::install($context);
    }

    /**
     * Update the plugin.
     *
     * @param Context\UpdateContext $context
     */
    public function update(Context\UpdateContext $context)
    {
        // update the plugin
        $updater = new Setup\Update(
            $this,
            $context,
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service'),
            $this->getPath() . '/'
        );
        $updater->update($context->getCurrentVersion());

        // call default updater
        parent::update($context);
    }

    /**
     * Uninstall the plugin.
     *
     * @param Context\UninstallContext $context
     *
     * @throws \Exception
     */
    public function uninstall(Context\UninstallContext $context)
    {
        // uninstall the plugin
        $uninstaller = new Setup\Uninstall(
            $this,
            $context,
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );
        $uninstaller->uninstall();

        // clear complete cache
        $context->scheduleClearCache($context::CACHE_LIST_ALL);

        // call default uninstaller
        parent::uninstall($context);
    }
}
