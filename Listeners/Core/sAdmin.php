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

namespace OstShippingCosts\Listeners\Core;

use Enlight_Hook_HookArgs as HookArgs;
use OstShippingCosts\Services\CalculatorServiceInterface;
use OstShippingCosts\Services\DispatchFilterServiceInterface;

class sAdmin
{
    /**
     * ...
     *
     * @var array
     */
    protected $configuration;

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
     * @param HookArgs $arguments
     */
    public function afterShippingCosts(HookArgs $arguments)
    {
        // get dispatch id
        $dispatchId = (int) Shopware()->Session()->offsetGet('sDispatch');

        /* @var $calculatorService CalculatorServiceInterface */
        $calculatorService = Shopware()->Container()->get('ost_shipping_costs.calculator_service');

        // get the shipping costs for this dispach method for the current basket
        $costs = $calculatorService->calculate($dispatchId);

        // no costs returned?!
        if ($costs === 0.0) {
            // nothing to do
            return;
        }

        // get return value
        $return = $arguments->getReturn();

        // get the tax
        $tax = (1 + ($return['tax'] / 100));

        // force valid tax if we have an zero shipping costs
        $tax = ($tax === 1.0) ? 1.19 : $tax;

        // add current surcharge to our costs
        $costs += $return['surcharge'];

        // set new return
        $return['value'] = round($costs, 2);
        $return['netto'] = round($costs / $tax, 2);
        $return['brutto'] = round($costs, 2);

        // set new value
        $arguments->setReturn($return);
    }

    /**
     * ...
     *
     * @param HookArgs $arguments
     */
    public function afterDispatches(HookArgs $arguments)
    {
        // get available shipping methods
        $methods = $arguments->getReturn();

        /* @var $dispatchFilterService DispatchFilterServiceInterface */
        $dispatchFilterService = Shopware()->Container()->get('ost_shipping_costs.dispatch_filter_service');

        // filter it
        $methods = $dispatchFilterService->filter($methods);

        // set new return value
        $arguments->setReturn($methods);
    }
}
