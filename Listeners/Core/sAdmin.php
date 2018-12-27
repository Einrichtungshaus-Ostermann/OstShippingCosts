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
            SELECT ost_shipping_costs_type
            FROM s_premium_dispatch_attributes
            WHERE dispatchID = :id
                AND ost_shipping_costs_status = 1
        ';
        $type = (int) Shopware()->Db()->fetchOne($query, ['id' => (int) $dispatchId]);

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
        $articles = ((count($articles['P']) > 0) && (count($articles['G']) === 0))
            ? $articles['P']
            : $articles['G'];

        // summed up shipping costs
        $costs = 0.0;

        // loop every article
        foreach ($articles as $article) {
            // and add costs multiplied with quantity
            $costs += $article['quantity'] * $article['costs'];
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

        // loop every method
        foreach ($methods as $key => $method) {
            // activated for this plugin?
            if ((int) $method['attribute']['ost_shipping_costs_status'] !== 1) {
                // nope
                continue;
            }

            /*
            // change the name based on articles
            $methods[$key]['name'] = (count($articles['G']) > 0)
                ? 'Hermes Spedition'
                : 'DHL Paket';
            */

            // remove with wrong typ
            if (count($articles['G']) > 0 && (int) $method['attribute']['ost_shipping_costs_type'] !== 2) {
                // remove it
                unset($methods[$key]);
            }

            // same with other
            if (count($articles['G']) === 0 && (int) $method['attribute']['ost_shipping_costs_type'] !== 1) {
                // remove it
                unset($methods[$key]);
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
}
