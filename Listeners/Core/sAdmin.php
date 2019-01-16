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
use OstShippingCosts\Services\ArticleServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;

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
     *
     * @throws \Enlight_Event_Exception
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function afterShippingCosts(HookArgs $arguments)
    {
        // get dispatch id
        $dispatchId = Shopware()->Session()->offsetGet('sDispatch');

        // check if our current dispatch method is activated for our plugin
        $query = '
            SELECT *
            FROM s_premium_dispatch_attributes
            WHERE dispatchID = :id
                AND ost_shipping_costs_status = 1
        ';
        $attributes = Shopware()->Db()->fetchRow($query, ['id' => (int) $dispatchId]);

        // set params
        $type = (int) $attributes['ost_shipping_costs_type'];
        $express = (bool) $attributes['ost_shipping_costs_express_status'];

        // remove if the attribute is not set for this dispach method
        if ($type === 0) {
            return;
        }

        // get basket articles
        $articles = $this->getArticles();

        // we only support truck articles
        if (count($articles['G']) > 0 && $type !== 2) {
            // remove it
            return;
        }

        // and only packages
        if (count($articles['G']) === 0 && $type !== 1) {
            // ignore it
            return;
        }

        // ignore p or only use g
        // if we have only p we do NOT sum up the articles so we only use the first article with 1 quantity
        $articles = ((count($articles['P']) > 0) && (count($articles['G']) === 0))
            ? array( array_merge( $articles['P'][0], array( 'quantity' => 1 ) ) )
            : $articles['G'];

        // summed up shipping costs
        $costs = 0.0;

        // loop every article
        foreach ($articles as $article) {
            // and add costs multiplied with quantity
            $costs += $article['quantity'] * $article['costs'];
        }

        // is this express dispatc method?!
        if ( $express === true ) {
            // add costs
            $costs += (float) $this->configuration['expressSurcharge'];
        }

        // get return value
        $return = $arguments->getReturn();

        // get the tax
        $tax = (1 + ($return['tax'] / 100));

        // force valid tax if we have an zero shipping costs
        $tax = ($tax === 1.0) ? 1.19 : $tax;

        // make it net to calculate it further
        $costs = $costs / $tax;

        // add surcharge to the costs
        $costs = round($costs + ($return['surcharge'] / $tax), 2);

        // set new return
        $return['value'] = round($costs * $tax, 2);
        $return['netto'] = round($costs, 2);
        $return['brutto'] = round($return['netto'] * $tax, 2);

        // set new value
        $arguments->setReturn($return);
    }

    /**
     * ...
     *
     * @param HookArgs $arguments
     *
     * @throws \Enlight_Event_Exception
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function afterDispatches(HookArgs $arguments)
    {
        // get available shipping methods
        $methods = $arguments->getReturn();

        // get our articles
        $articles = $this->getArticles();

        // current zip code
        $zip = (!empty(Shopware()->Session()->get('sRegister')['shipping']['zipcode']))
            ? (string) Shopware()->Session()->get('sRegister')['shipping']['zipcode']
            : (string) Shopware()->Session()->get('sRegister')['billing']['zipcode'];

        // loop every method
        foreach ($methods as $key => $method) {
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
                    unset($methods[$key]);

                    // and next
                    continue;
                }

                // is this express delivery?
                if ((bool) $method['attribute']['ost_shipping_costs_express_status'] === true) {
                    // we need every article to be available in witten
                    // @todo we dont have an attribute for this yet
                }

                // allright... done with p
                continue;
            }

            // via truck
            if ((int) $method['attribute']['ost_shipping_costs_type'] === 2) {
                // we need at least one g article
                if (count($articles['G']) === 0) {
                    // remove it
                    unset($methods[$key]);

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
                            unset($methods[$key]);
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
                        unset($methods[$key]);
                    }
                } else {
                    // we remove every dispatch method which IS for self delivery
                    if ((bool) $method['attribute']['ost_shipping_costs_selfdelivery_status'] === true) {
                        // remove it
                        unset($methods[$key]);
                    }
                }
            }
        }

        // set new return value
        $arguments->setReturn($methods);
    }

    /**
     * ...
     *
     * @throws \Enlight_Event_Exception
     * @throws \Enlight_Exception
     * @throws \Zend_Db_Adapter_Exception
     *
     * @return array
     */
    private function getArticles()
    {
        /* @var $articleService ArticleServiceInterface */
        $articleService = Shopware()->Container()->get('ost_shipping_costs.article_service');

        // empty array for different dispatch types
        $articles = [
            'P' => [],
            'G' => []
        ];

        // get the current basket
        $basket = Shopware()->Modules()->Basket()->sGetBasket();

        // loop every article
        foreach ($basket['content'] as $article) {
            // valid attribute?
            if (!isset($article['additional_details']['attributes']) || (!is_array($article['additional_details']['attributes'])) || (!$article['additional_details']['attributes']['core'] instanceof Attribute)) {
                continue;
            }

            /* @var Attribute $attributes */
            $attributes = $article['additional_details']['attributes']['core'];

            // get type and costs
            $type = $articleService->getDispatchType($attributes->toArray());
            $costs = $articleService->getShippingCosts($attributes->toArray());

            // valid type?!
            if (!in_array($type, ['P', 'G'])) {
                // ignore it
                continue;
            }

            // add article to our array
            array_push($articles[$type], [
                'number'   => $article['ordernumber'],
                'quantity' => (int) $article['quantity'],
                'costs'    => $costs,
            ]);
        }

        // return them
        return $articles;
    }

    /**
     * ...
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
