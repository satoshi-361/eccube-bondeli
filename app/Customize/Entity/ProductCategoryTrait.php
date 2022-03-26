<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\ProductCategory")
 */
trait ProductCategoryTrait
{
    /**
     * @var Plugin\restaurant_food\Entity\Config
     *
     * @ORM\ManyToOne(targetEntity="Plugin\restaurant_food\Entity\Config", inversedBy="RestaurantCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $Restaurant;

    /**
     * Set restaurant.
     *
     * @param Plugin\restaurant_food\Entity\Config|null $restaurant
     *
     * @return ProductCategory
     */
    public function setRestaurant(\Plugin\restaurant_food\Entity\Config $restaurant = null)
    {
        $this->Restaurant = $restaurant;
        return $this;
    }

    /**
     * Get restaurant.
     *
     * @return Plugin\restaurant_food\Entity\Config|null
     */
    public function getRestaurant()
    {
        return $this->Restaurant;
    }
}