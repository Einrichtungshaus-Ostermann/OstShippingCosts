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

namespace OstShippingCosts\Services;

class ArticleService implements ArticleServiceInterface
{
    /**
     * ...
     *
     * @var array
     */
    private $configuration;

    /**
     * ...
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        // set params
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getDispatchType(array $attributes): string
    {
        // return by attribute
        return (string) $attributes[$this->configuration['attributeDispatchType']];
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCosts(array $attributes): float
    {
        // fullservice price?
        if ((int) $attributes[$this->configuration['attributeTag']] === 2) {
            // no costs
            return 0.0;
        }

        // always return calculated shipping costs
        return (float) $attributes[$this->configuration['attributeCalculatedShippingCosts']];
    }

    /**
     * {@inheritdoc}
     */
    public function isAddition(array $attributes): bool
    {
        // we never ever have addition if we are ostermann with online shop
        if ((int) Shopware()->Container()->get('ost_foundation.configuration')['company'] === 1 && (string) Shopware()->Container()->get('ost_foundation.configuration')['shop'] === 'online') {
            // nope
            return false;
        }

        // trends and oms with iwm shipping costs is always addition
        if (in_array((int) Shopware()->Container()->get('ost_foundation.configuration')['company'], array(3, 99)) && (float) $attributes[$this->configuration['attributeDispatchCosts']] > 0) {
            // always addition
            return true;
        }

        // it is always addition if: type = p, context = inhouse, iwm shipping costs > 0
        if ((string) Shopware()->Container()->get('ost_foundation.configuration')['shop'] === 'inhouse' && $this->getDispatchType($attributes) == "P" & (float) $attributes[$this->configuration['attributeDispatchCosts']] > 0) {
            // yes... always addition
            return true;
        }

        // return by attribute
        return (int) $attributes[$this->configuration['attributeDispatchAddition']] === 1;
    }
}
