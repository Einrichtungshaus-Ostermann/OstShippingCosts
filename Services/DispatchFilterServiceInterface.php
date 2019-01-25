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

interface DispatchFilterServiceInterface
{
    /**
     * ...
     *
     * @param array $dispatchMethods
     *
     * @return array
     */
    public function filter(array $dispatchMethods): array;
}
