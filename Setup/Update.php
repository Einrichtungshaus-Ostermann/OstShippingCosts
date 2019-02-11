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

class Update
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
     * @var string
     */
    protected $pluginDir;

    /**
     * ...
     *
     * @param Plugin         $plugin
     * @param InstallContext $context
     * @param ModelManager   $modelManager
     * @param CrudService    $crudService
     * @param string         $pluginDir
     */
    public function __construct(Plugin $plugin, InstallContext $context, ModelManager $modelManager, CrudService $crudService, $pluginDir)
    {
        // set params
        $this->plugin = $plugin;
        $this->context = $context;
        $this->modelManager = $modelManager;
        $this->crudService = $crudService;
        $this->pluginDir = $pluginDir;
    }

    /**
     * ...
     */
    public function install()
    {
        // install updates
        $this->update('0.0.0');
    }

    /**
     * ...
     *
     * @param string $version
     */
    public function update($version)
    {
        // switch old version
        switch ($version) {
            case '0.0.0':
            case '1.0.0':
                $this->updateAttributes();
            case '1.1.0':
            case '1.1.1':
            case '1.1.2':
                $this->updateAttributes();
                $this->updateSql('1.2.0');
            case '1.2.0':
            case '1.2.1':
            case '1.2.2':
            case '1.2.3':
            case '1.2.4':
            case '1.2.5':
                $this->updateAttributes();
            case '1.3.0':
            case '1.4.0':
            case '1.4.1':
            case '1.4.2':
            case '1.4.3':
            case '1.4.4':
            case '1.4.5':
            case '1.4.6':
                $this->updateAttributes();
                $this->updateSql('1.5.0');
        }
    }

    /**
     * ...
     *
     * @throws \Exception
     */
    private function updateAttributes()
    {
        // ...
        foreach (Install::$attributes as $table => $attributes) {
            foreach ($attributes as $attribute) {
                try {
                    $this->crudService->update(
                        $table,
                        $attribute['column'],
                        $attribute['type'],
                        $attribute['data']
                    );
                } catch (\Exception $exception) {
                }
            }
        }

        // ...
        $this->modelManager->generateAttributeModels(array_keys(Install::$attributes));
    }

    /**
     * ...
     *
     * @param string $version
     */
    private function updateSql($version)
    {
        // get the sql query for this update
        $sql = @file_get_contents(rtrim($this->pluginDir, '/') . '/Setup/Update/update-' . $version . '.sql');

        // execute the query and catch any db exception and ignore it
        try {
            Shopware()->Db()->exec($sql);
        } catch (Exception $exception) {
        }
    }
}
