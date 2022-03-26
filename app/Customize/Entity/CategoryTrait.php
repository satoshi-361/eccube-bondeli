<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Category")
 */
trait CategoryTrait
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\restaurant_food\Entity\RestaurantCategory", mappedBy="Category", fetch="EXTRA_LAZY")
     */
    private $RestaurantCategories;

    public function __construct()
    {
        $this->RestaurantCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add restaurantCategory.
     *
     * @param \Plugin\restaurant_food\Entity\RestaurantCategory $restaurantCategory
     *
     * @return Category
     */
    public function addRestaurantCategory(\Plugin\restaurant_food\Entity\RestaurantCategory $restaurantCategory)
    {
        $this->RestaurantCategories[] = $restaurantCategory;
        return $this;
    }

    /**
     * Remove restaurantCategory.
     *
     * @param \Plugin\restaurant_food\Entity\RestaurantCategory $restaurantCategory
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRestaurantCategory(\Plugin\restaurant_food\Entity\RestaurantCategory $restaurantCategory)
    {
        return $this->RestaurantCategories->removeElement($restaurantCategory);
    }

    /**
     * Get restaurantCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantCategories()
    {
        return $this->RestaurantCategories;
    }
}