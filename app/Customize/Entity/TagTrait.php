<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Tag")
 */
trait TagTrait
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\restaurant_food\Entity\RestaurantTag", mappedBy="Tag", fetch="EXTRA_LAZY")
     */
    private $RestaurantTags;

    public function __construct()
    {
        $this->RestaurantTags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add RestaurantTag.
     *
     * @param \Plugin\restaurant_food\Entity\RestaurantTag $RestaurantTag
     *
     * @return Tag
     */
    public function addRestaurantTag(\Plugin\restaurant_food\Entity\RestaurantTag $RestaurantTag)
    {
        $this->RestaurantTags[] = $RestaurantTag;
        return $this;
    }

    /**
     * Remove RestaurantTag.
     *
     * @param \Plugin\restaurant_food\Entity\RestaurantTag $RestaurantTag
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRestaurantTag(\Plugin\restaurant_food\Entity\RestaurantTag $RestaurantTag)
    {
        return $this->RestaurantTags->removeElement($RestaurantTag);
    }

    /**
     * Get RestaurantTags.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantTags()
    {
        return $this->RestaurantTags;
    }
}