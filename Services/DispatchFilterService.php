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

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

class DispatchFilterService implements DispatchFilterServiceInterface
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
    public function filter(array $dispatchMethods): array
    {
        /* @var $basketService BasketServiceInterface */
        $basketService = Shopware()->Container()->get('ost_shipping_costs.basket_service');

        // get our articles
        $articles = $basketService->getArticles();

        // current zip code
        $zip = (!empty(Shopware()->Session()->get('sRegister')['shipping']['zipcode']))
            ? (string) Shopware()->Session()->get('sRegister')['shipping']['zipcode']
            : (string) Shopware()->Session()->get('sRegister')['billing']['zipcode'];

        // loop every method
        foreach ($dispatchMethods as $key => $method) {
            // activated for this plugin?
            if ((int) $method['attribute']['ost_shipping_costs_status'] !== 1) {
                // nope
                continue;
            }

            // default package
            if ((int) $method['attribute']['ost_shipping_costs_type'] === 1) {
                // only active if we have only p articles
                if (count($articles['G']) > 0) {
                    // remove it
                    unset($dispatchMethods[$key]);

                    // and next
                    continue;
                }

                // is this express delivery?
                if ((bool) $method['attribute']['ost_shipping_costs_express_status'] === true) {
                    // we need every article to be available in this witten (because we send every package from witten)
                    $stock = array_column(array_column($articles['P'], 'attributes'), $this->configuration['expressStockAttribute']);

                    // valid stocks
                    $validStocks = array_map(function ($quantity) { return ((int) $quantity > 0) ? 1 : 0; }, $stock);

                    // every article has to be available
                    if (array_sum($validStocks) < count($validStocks)) {
                        // sry... not enough stock
                        unset($dispatchMethods[$key]);

                        // next
                        continue;
                    }
                }

                // allright... done with p
                continue;
            }

            // via truck
            if ((int) $method['attribute']['ost_shipping_costs_type'] === 2) {
                // we need at least one g article
                if (count($articles['G']) === 0) {
                    // remove it
                    unset($dispatchMethods[$key]);

                    // and next
                    continue;
                }

                // drop method first and ignore self delivery after
                if ($this->configuration['dropStatus'] === true && substr_count($this->configuration['dropStartDate'], 'T00:00:00') > 0 && substr_count($this->configuration['dropEndDate'], 'T00:00:00') > 0) {
                    // create date objects
                    $start = new \DateTime($this->configuration['dropStartDate']);
                    $end = new \DateTime($this->configuration['dropEndDate']);

                    // are we within the dates?
                    if (time() > $start->getTimestamp() && time() < $end->getTimestamp() && $this->isValidDropBasket()) {
                        // we remove every dispatch method which is NOT a drop method
                        if ((bool) $method['attribute']['ost_shipping_costs_drop_status'] === false) {
                            // remove it
                            unset($dispatchMethods[$key]);
                        }

                        // ignore every other check and get on with the next
                        continue;
                    }
                }

                // check for self delivery
                $query = '
                    SELECT COUNT(*)
                    FROM ost_shipping_costs_selfdelivery
                    WHERE zip LIKE :zip
                ';
                $count = (int) Shopware()->Db()->fetchOne($query, ['zip' => $zip]);

                // do we have self delivery?!
                if ($count > 0) {
                    // we remove every dispatch method which is NOT for self delivery
                    if ((bool) $method['attribute']['ost_shipping_costs_selfdelivery_status'] === false) {
                        // remove it
                        unset($dispatchMethods[$key]);
                    }
                } else {
                    // we remove every dispatch method which IS for self delivery
                    if ((bool) $method['attribute']['ost_shipping_costs_selfdelivery_status'] === true) {
                        // remove it
                        unset($dispatchMethods[$key]);
                    }
                }
            }
        }

        // return filtered dispatch methods
        return $dispatchMethods;
    }

    /**
     * ...
     *
     * @throws \Enlight_Event_Exception
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     *
     * @return bool
     */
    private function isValidDropBasket(): bool
    {
        // get the current basket
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        // max articles
        if ((!is_array($basket)) || (!isset($basket['content'])) || (!is_array($basket['content'])) || (count($basket['content']) > (int) $this->configuration['dropMaxArticles'])) {
            // nope
            return false;
        }

        // max amount
        if ((float) $basket['AmountNumeric'] > (float) $this->configuration['dropMaxBasket']) {
            // nope
            return false;
        }

        // exclude these
        $hwg = explode('<br>', nl2br($this->configuration['dropExcludeHwg'], false));
        $supplier = explode('<br>', nl2br($this->configuration['dropExcludeSupplier'], false));

        // trim both
        $hwg = array_map(function ($current) { return trim($current); }, $hwg);
        $supplier = array_map(function ($current) { return trim($current); }, $supplier);

        // loop every article
        foreach ($basket['content'] as $article) {
            // valid attribute?
            if (!isset($article['additional_details']['attributes']) || (!is_array($article['additional_details']['attributes'])) || (!$article['additional_details']['attributes']['core'] instanceof Attribute)) {
                continue;
            }

            /* @var Attribute $attributes */
            $attributes = $article['additional_details']['attributes']['core'];

            // invalid hwg?
            if (in_array($attributes->get('attributeHwg'), $hwg)) {
                // invalid
                return false;
            }

            // loop every invalid supplier
            foreach ($supplier as $supplierName) {
                // found in this article?
                if (substr_count(strtolower($supplierName), $article['additional_details']['supplierName']) > 0) {
                    // invalid
                    return false;
                }
            }
        }

        // complete basket is fine
        return true;
    }
}
