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

class BasketService implements BasketServiceInterface
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
    public function getArticles(): array
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
            $isAddition = $articleService->isAddition($attributes->toArray());

            // valid type?!
            if (!in_array($type, ['P', 'G'])) {
                // ignore it
                continue;
            }

            // add article to our array
            array_push($articles[$type], [
                'number'     => $article['ordernumber'],
                'quantity'   => (int) $article['quantity'],
                'costs'      => $costs,
                'addition'   => $isAddition,
                'attributes' => $attributes->toArray()
            ]);
        }

        // return them
        return $articles;
    }
}
