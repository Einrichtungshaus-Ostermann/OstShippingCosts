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

interface ArticleServiceInterface
{
    /**
     * ...
     *
     * @param array $attributes
     *
     * @return string
     */
    public function getDispatchType(array $attributes): string;

    /**
     * ...
     *
     * @param array $attributes
     *
     * @return float
     */
    public function getShippingCosts(array $attributes): float;

    /**
     * ...
     *
     * @param array $attributes
     *
     * @return bool
     */
    public function isAddition(array $attributes): bool;
}
