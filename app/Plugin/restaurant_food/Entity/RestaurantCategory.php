<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\restaurant_food\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RestaurantCategory
 *
 * @ORM\Table(name="plg_restaurant_category")
 * @ORM\Entity(repositoryClass="Plugin\restaurant_food\Repository\RestaurantCategoryRepository")
 */ 
class RestaurantCategory extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="restaurant_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $restaurant_id;
    
    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $category_id;

    /**
     * @var \Plugin\restaurant_food\Entity\Config
     *
     * @ORM\ManyToOne(targetEntity="Plugin\restaurant_food\Entity\Config", inversedBy="RestaurantCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     * })
     */
    private $Restaurant;

    /**
     * @var \Eccube\Entity\Category
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category", inversedBy="RestaurantCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     * })
     */
    private $Category;

    /**
     * Set restaurantId.
     *
     * @param int $restaurantId
     *
     * @return RestaurantCategory
     */
    public function setRestaurantId($restaurantId)
    {
        $this->restaurant_id = $restaurantId;
        return $this;
    }

    /**
     * Get restaurantId.
     *
     * @return int
     */
    public function getRestaurantId()
    {
        return $this->restaurant_id;
    }

    /**
     * Set categoryId.
     *
     * @param int $categoryId
     *
     * @return RestaurantCategory
     */
    public function setCategoryId($categoryId)
    {
        $this->category_id = $categoryId;
        return $this;
    }

    /**
     * Get categoryId.
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->category_id;
    }

    /**
     * Set product.
     *
     * @param \Plugin\restaurant_food\Entity\Config|null $product
     *
     * @return RestaurantCategory
     */
    public function setRestaurant(\Plugin\restaurant_food\Entity\Config $restaurant = null)
    {
        $this->Restaurant = $restaurant;
        return $this;
    }

    /**
     * Get product.
     *
     * @return \Plugin\restaurant_food\Entity\Config|null
     */
    public function getRestaurant()
    {
        return $this->Restaurant;
    }

    /**
     * Set category.
     *
     * @param \Eccube\Entity\Category|null $category
     *
     * @return RestaurantCategory
     */
    public function setCategory(\Eccube\Entity\Category $category = null)
    {
        $this->Category = $category;
        return $this;
    }

    /**
     * Get category.
     *
     * @return \Eccube\Entity\Category|null
     */
    public function getCategory()
    {
        return $this->Category;
    }
}