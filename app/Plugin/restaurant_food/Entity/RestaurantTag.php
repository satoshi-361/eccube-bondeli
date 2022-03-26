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
 * RestaurantTag
 *
 * @ORM\Table(name="plg_restaurant_tag")
 * @ORM\Entity(repositoryClass="Plugin\restaurant_food\Repository\RestaurantTagRepository")
 */
class RestaurantTag extends \Eccube\Entity\AbstractEntity
{
    /**
     * Get tag_id
     * use csv export
     *
     * @return integer
     */
    public function getTagId()
    {
        if (empty($this->Tag)) {
            return null;
        }
        return $this->Tag->getId();
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;
    /**
     * @ORM\ManyToOne(targetEntity="Plugin\restaurant_food\Entity\Config", inversedBy="RestaurantTag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     * })
     */
    private $Restaurant;
    /**
     * @var \Eccube\Entity\Tag
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Tag", inversedBy="RestaurantTags")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $Tag;
    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return RestaurantTag
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;
        return $this;
    }
    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }
    /**
     * Set restaurant.
     *
     * @param Plugin\restaurant_food\Entity\Config|null $restaurant
     *
     * @return RestaurantTag
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
     * Set tag.
     *
     * @param \Eccube\Entity\Tag|null $tag
     *
     * @return RestaurantTag
     */
    public function setTag(\Eccube\Entity\Tag $tag = null)
    {
        $this->Tag = $tag;
        return $this;
    }
    /**
     * Get tag.
     *
     * @return \Eccube\Entity\Tag|null
     */
    public function getTag()
    {
        return $this->Tag;
    }
}
