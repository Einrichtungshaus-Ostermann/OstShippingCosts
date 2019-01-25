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

class CalculatorService implements CalculatorServiceInterface
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
    public function calculate(int $dispatchId): float
    {
        // check if our current dispatch method is activated for our plugin
        $query = '
            SELECT *
            FROM s_premium_dispatch_attributes
            WHERE dispatchID = :id
        ';
        $attributes = Shopware()->Db()->fetchRow($query, ['id' => $dispatchId]);

        // not found or not activated?
        if ((!is_array($attributes)) || $attributes['ost_shipping_costs_status'] === '0') {
            // nothing to calculate
            return 0.0;
        }

        /* @var $basketService BasketServiceInterface */
        $basketService = Shopware()->Container()->get('ost_shipping_costs.basket_service');

        // get our articles
        $articles = $basketService->getArticles();

        // we only calculate if this dispatch method fits to our basket
        if ((count($articles['G']) > 0 && (int) $attributes['ost_shipping_costs_type'] !== 2) || (count($articles['G']) === 0 && (int) $attributes['ost_shipping_costs_type'] !== 1)) {
            // we dont need to calculate
            return 0.0;
        }

        // split by addition or not
        $addition = [
            0 => [
                'P' => [],
                'G' => []
            ],
            1 => []
        ];

        // ...
        foreach ($articles as $dispatchType => $arr) {
            // loop every article for the current dispatch type
            foreach ($arr as $article) {
                // with addition
                if ($article['addition'] === true) {
                    // add without check for type
                    array_push($addition[1], $article);
                } else {
                    // add with p or g
                    array_push($addition[0][$dispatchType], $article);
                }
            }
        }

        // zero costs as start
        $costs = 0.0;

        // firstly: we have no G articles
        if (count($addition[0]['G']) === 0) {
            // always only pay for the highest package rate
            $costsArr = array_column($addition[0]['P'], 'costs');

            // get the highest as costs
            $costs = (float) max($costsArr);
        } else {
            // we have g articles and we completly ignore p articles.
            // we only use the highest cost in online shops and we always
            // sum up everything in inhouse projects
            if ((string) Shopware()->Container()->get('ost_foundation.configuration')['shop'] === 'online') {
                // always only pay for the highest truck rate
                $costsArr = array_column($addition[0]['G'], 'costs');

                // get the highest as costs
                $costs = (float) max($costsArr);
            } else {
                // in inhouse: just put everything in addition
                foreach ($addition[0]['G'] as $article) {
                    // add it
                    array_push($addition[1], $article);
                }
            }
        }

        // now add every addition article
        foreach ($addition[1] as $article) {
            // add it
            $costs += (int) $article['quantity'] * (float) $article['costs'];
        }

        // and we finally have the costs from the basket plus every addition article
        // depending on the context (inhouse or online)
        return $costs;
    }
}
