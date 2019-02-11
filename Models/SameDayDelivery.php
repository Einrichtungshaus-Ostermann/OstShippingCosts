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

namespace OstShippingCosts\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="ost_shipping_costs_samedaydelivery")
 */
class SameDayDelivery extends ModelEntity
{
    /**
     * Auto-generated id.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * ...
     *
     * @var string
     *
     * @ORM\Column(name="zip", type="string", nullable=false, length=16)
     */
    private $zip;

    /**
     * ...
     */
    public function __construct()
    {
    }

    /**
     * Getter method for the property.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Setter method for the property.
     *
     * @param string $zip
     */
    public function setZip(string $zip)
    {
        $this->zip = $zip;
    }
}
