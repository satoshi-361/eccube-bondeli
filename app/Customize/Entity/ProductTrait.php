<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="food_type", type="string")
     */
    private $food_type;

    /**
     * @var int
     *
     * @ORM\Column(name="orderable_date", type="integer", nullable=true)
     */
    private $orderable_date;

    /**
     * @var int
     *
     * @ORM\Column(name="visible_price", type="integer")
     */
    private $visible_price;

    /**
     * @var string
     *
     * @ORM\Column(name="food_image", type="string", nullable=true)
     */
    private $food_image;

    /**
     * @var bool
     * 
     * @ORM\Column(name="is_visible", type="boolean")
     */
    private $is_visible = true;

    /**
     * @var string
     * 
     * @ORM\Column(name="dressing", type="string", nullable=true)
     */
    private $dressing;

    /**
     * @var int
     *
     * @ORM\Column(name="upper_limit", type="integer")
     */
    private $upper_limit = 10;

    /**
     * @var Plugin\restaurant_food\Entity\Config
     *
     * @ORM\ManyToOne(targetEntity="Plugin\restaurant_food\Entity\Config", inversedBy="Products")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $Restaurant;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Eccube\Entity\ProductClass", mappedBy="Product", cascade={"persist","remove"}, orphanRemoval=true)
     */
    private $ProductClasses;

    /**
     * set food_type
     * 
     * @param string|null $food_type
     * 
     * @return Product
     */
    public function setFoodType($food_type)
    {
        $this->food_type = $food_type;
        return $this;
    }

    /**
     * Get food_type 
     * 
     * @return string|null
     */
    public function getFoodType()
    {
        return $this->food_type;
    }

    /**
     * set orderable_date
     * 
     * @param int|null $orderable_date
     * 
     * @return Product
     */
    public function setOrderableDate($orderable_date)
    {
        $this->orderable_date = $orderable_date;
        return $this;
    }

    /**
     * Get orderable_date 
     * 
     * @return int|null
     */
    public function getOrderableDate()
    {
        return $this->orderable_date;
    }

    /**
     * set visible_price
     * 
     * @param int|null $visible_price
     * 
     * @return Product
     */
    public function setVisiblePrice($visible_price)
    {
        $this->visible_price = $visible_price;
        return $this;
    }

    /**
     * Get visible_price 
     * 
     * @return int|null
     */
    public function getVisiblePrice()
    {
        return $this->visible_price;
    }

    /**
     * set food_image
     * 
     * @param string|null $food_image
     * 
     * @return Product
     */
    public function setFoodImage($food_image)
    {
        $this->food_image = $food_image;
        return $this;
    }

    /**
     * Get food_image 
     * 
     * @return string|null
     */
    public function getFoodImage()
    {
        return $this->food_image;
    }

    /**
     * set visible
     * 
     * @param bool $is_visible
     * 
     * @return Product
     */
    public function setIsVisible($is_visible)
    {
        $this->is_visible = $is_visible;
        return $this;
    }

    /**
     * Get is_visible 
     * 
     * @return bool
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }

    /**
     * set upper_limit
     * 
     * @param int $upper_limit
     * 
     * @return Product
     */
    public function setUpperLimit($upper_limit)
    {
        $this->upper_limit = $upper_limit;
        return $this;
    }

    /**
     * Get upper_limit 
     * 
     * @return int
     */
    public function getUpperLimit()
    {
        return $this->upper_limit;
    }

    /**
     * Set restaurant.
     *
     * @param Plugin\restaurant_food\Entity\Config|null $restaurant
     *
     * @return Product
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

    /**
     * set dressing
     * 
     * @param string|null $dressing
     * 
     * @return Product
     */
    public function setDressing($dressing)
    {
        $this->dressing = $dressing;
        return $this;
    }

    /**
     * Get dressing 
     * 
     * @return string|null
     */
    public function getDressing()
    {
        return $this->dressing;
    }
}