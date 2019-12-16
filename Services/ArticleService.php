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
     * Every constant for every shop in the foundation context configuration.
     *
     * @var integer
     */
    const COMPANY_OSTERMANN = 1;
    const COMPANY_TRENDS = 3;
    const COMPANY_MOEBEL_SHOP = 99;

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
        // get the current company and context
        $company = (int) Shopware()->Container()->get('ost_foundation.configuration')['company'];
        $context = (string) Shopware()->Container()->get('ost_foundation.configuration')['shop'];

        // get iwm data
        $iwmCosts = (float) $attributes[$this->configuration['attributeDispatchCosts']];
        $iwmAddition = (int) $attributes[$this->configuration['attributeDispatchAddition']];

        // P niemals addition

        // G:
        // - im OMS NIE addition
        // - in trends NIE addition
        // - in ostermann NIE addition

        // stand 26. november 2019
        //
        // -> preise für oms korrigieren (abolhpreis korrekt an oms übergeben)
        //    -> gantenberg korrigiert access abfragen
        //    -> aktuell überschreibt vollservicepreis auch den abholpreis (= preis 1)
        //
        // aktuelle zwischenlösung:
        // -> KEINE addition in KEINEM online-shop

        // never online
        if ($context === 'online') {
            // never
            return false;
        }

        // it is always addition if: type = p, context = inhouse, iwm shipping costs > 0
        if ($context === 'inhouse' && $this->getDispatchType($attributes) === "P" && $iwmCosts > 0) {
            // yes... always addition
            return true;
        }

        // return by attribute
        return $iwmAddition === 1;
    }
}
