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
     * ...
     *
     * @param array $attributes
     *
     * @return string
     */
    public function getDispatchType(array $attributes): string
    {
        // return by attribute
        return (string) $attributes[$this->configuration['attributeDispatchType']];
    }

    /**
     * ...
     *
     * @param array $attributes
     *
     * @return float
     */
    public function getShippingCosts(array $attributes): float
    {
        // fullservice price?
        if ((int) $attributes[$this->configuration['attributeTag']] === 2) {
            // no costs
            return 0.0;
        }

        // return by dispatch type
        return ($this->getDispatchType($attributes) === 'P')
            ? (float) $attributes[$this->configuration['attributeCalculatedShippingCosts']]
            : (float) $attributes[$this->configuration['attributeErpShippingCosts']];
    }
}
