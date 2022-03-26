<?php

namespace Plugin\restaurant_food\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_restaurant")
 * @ORM\Entity(repositoryClass="Plugin\restaurant_food\Repository\ConfigRepository")
 */
class Config extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=255)
     */
    private $company_name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="postal_code", type="string", length=8, nullable=true)
     */
    private $postal_code;

    /**
     * @var string|null
     *
     * @ORM\Column(name="addr01", type="string", length=255, nullable=true)
     */
    private $addr01;

    /**
     * @var string|null
     *
     * @ORM\Column(name="addr02", type="string", length=255, nullable=true)
     */
    private $addr02;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=true)
     */
    private $password;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone_number", type="string", length=14, nullable=true)
     */
    private $phone_number;

    /**
     * @var string
     *
     * @ORM\Column(name="explanation", type="string", nullable=true)
     */
    private $explanation;

    /**
     * @var int
     *
     * @ORM\Column(name="deliverable", type="integer", nullable=true)
     */
    private $deliverable;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline_start", type="datetimetz")
     */
    private $deadline_start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline_end", type="datetimetz")
     */
    private $deadline_end;

        /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline_start1", type="datetimetz")
     */
    private $deadline_start1;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deadline_end1", type="datetimetz")
     */
    private $deadline_end1;

    /**
     * @var string
     *
     * @ORM\Column(name="date_week", type="string", nullable=true)
     */
    private $date_week;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_account", type="string", nullable=true)
     */
    private $bank_account;

    /**
     * @var int
     *
     * @ORM\Column(name="sales_amount", type="integer", options={"unsigned":true}, nullable=true)
     */
    private $sales_amount;

    /**
     * @var int
     *
     * @ORM\Column(name="back_rate", type="integer", options={"unsigned":true}, nullable=true)
     */
    private $back_rate;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_fees", type="string", nullable=true)
     */
    private $delivery_fees;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", nullable=true)
     */
    private $content;

    /**
     * @var string
     * 
     * @ORM\Column(name="regist_code", type="string", length=255, nullable=true)
     */
    private $regist_code;

    /**
     * @var int
     * 
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @var int
     * 
     * @ORM\Column(name="lower_price_limit", type="integer")
     */
    private $lower_price_limit = 5000;

    /**
     * @var string
     * 
     * @ORM\Column(name="deliverable_area", type="string")
     */
    private $deliverable_area = '中央区, 千代田区, 港区, 江東区, 文京区, 品川区, 目黒区, 渋谷区, 新宿区';


    /**
     * @var string
     * 
     * @ORM\Column(name="food_type_list", type="string")
     */
    private $food_type_list = '前菜, 主菜, デザート';

    /**
     * @var \Eccube\Entity\Master\Pref
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Pref")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pref_id", referencedColumnName="id")
     * })
     */
    private $Pref;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Eccube\Entity\ProductImage", mappedBy="Restaurant", cascade={"remove"})
     * @ORM\OrderBy({
     *     "sort_no"="ASC"
     * })
     */
    private $RestaurantImage;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Eccube\Entity\Product", mappedBy="Restaurant", cascade={"remove"})
     */
    private $Products;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="\Plugin\restaurant_food\Entity\RestaurantCategory", mappedBy="Restaurant", cascade={"persist","remove"})
     */
    private $RestaurantCategories;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Plugin\restaurant_food\Entity\RestaurantTag", mappedBy="Restaurant", cascade={"remove"})
     */
    private $RestaurantTag;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->RestaurantImage = new \Doctrine\Common\Collections\ArrayCollection();
        $this->Products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->RestaurantTag =  new \Doctrine\Common\Collections\ArrayCollection();
        $this->RestaurantCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name01.
     *
     * @param string $name
     *
     * @return Restaurant
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name01.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set postal_code.
     *
     * @param string|null $postal_code
     *
     * @return Restaurant
     */
    public function setPostalCode($postal_code = null)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * Get postal_code.
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set addr01.
     *
     * @param string|null $addr01
     *
     * @return Restaurant
     */
    public function setAddr01($addr01 = null)
    {
        $this->addr01 = $addr01;
        return $this;
    }

    /**
     * Get addr01.
     *
     * @return string|null
     */
    public function getAddr01()
    {
        return $this->addr01;
    }

    /**
     * Set addr02.
     *
     * @param string|null $addr02
     *
     * @return Restaurant
     */
    public function setAddr02($addr02 = null)
    {
        $this->addr02 = $addr02;
        return $this;
    }

    /**
     * Get addr02.
     *
     * @return string|null
     */
    public function getAddr02()
    {
        return $this->addr02;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Restaurant
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone_number.
     *
     * @param string|null $phone_number
     *
     * @return Restaurant
     */
    public function setPhoneNumber($phone_number = null)
    {
        $this->phone_number = $phone_number;
        return $this;
    }

    /**
     * Get phone_number.
     *
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * Set explanation.
     *
     * @param string|null $explanation
     *
     * @return Restaurant
     */
    public function setExplanation($explanation = null)
    {
        $this->explanation = $explanation;
        return $this;
    }
    
    /**
     * Get explanation.
     *
     * @return string|null
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * Set deliverable.
     *
     * @param int|null $deliverable
     *
     * @return Restaurant
     */
    public function setDeliverable($deliverable = null)
    {
        $this->deliverable = $deliverable;
        return $this;
    }
    
    /**
     * Get deliverable.
     *
     * @return int|null
     */
    public function getDeliverable()
    {
        return $this->deliverable;
    }

    /**
     * @var boolean
     * @ORM\Column(name="is_deliverable_one", type="boolean", nullable=false, options={"default"=0})
     */
    private $is_deliverable_one = false;

    /**
     * Set deadline_start.
     *
     * @param \DateTime $deadline_start
     *
     * @return Restaurant
     */
    public function setDeadlineStart($deadline_start)
    {
        $this->deadline_start = $deadline_start;
        return $this;
    }

    /**
     * Get \DateTime.
     *
     * @return int
     */
    public function getDeadlineStart()
    {
        return $this->deadline_start;
    }

    /**
     * Set deadline_end.
     *
     * @param \DateTime $deadline_end
     *
     * @return Restaurant
     */
    public function setDeadlineEnd($deadline_end)
    {
        $this->deadline_end = $deadline_end;
        return $this;
    }

    /**
     * Get \DateTime.
     *
     * @return int
     */
    public function getDeadlineEnd()
    {
        return $this->deadline_end;
    }

    /**
     * Set deadline_start1
     *
     * @param \DateTime $deadline_start1
     *
     * @return Restaurant
     */
    public function setDeadlineStart1($deadline_start1)
    {
        $this->deadline_start1 = $deadline_start1;
        return $this;
    }

    /**
     * Get \DateTime.
     *
     * @return int
     */
    public function getDeadlineStart1()
    {
        return $this->deadline_start1;
    }

    /**
     * Set deadline_end1
     *
     * @param \DateTime $deadline_end1
     *
     * @return Restaurant
     */
    public function setDeadlineEnd1($deadline_end1)
    {
        $this->deadline_end1 = $deadline_end1;
        return $this;
    }

    /**
     * Get \DateTime.
     *
     * @return int
     */
    public function getDeadlineEnd1()
    {
        return $this->deadline_end1;
    }

    /**
     * Set date_week.
     *
     * @param string $date_week
     *
     * @return Restaurant
     */
    public function setDateWeek($date_week)
    {
        $this->date_week = $date_week;
        return $this;
    }

    /**
     * Get date_week.
     *
     * @return string
     */
    public function getDateWeek()
    {
        return $this->date_week;
    }

    /**
     * Set bank_account.
     *
     * @param string|null $bank_account
     *
     * @return Restaurant
     */
    public function setBankAccount($bank_account = null)
    {
        $this->bank_account = $bank_account;
        return $this;
    }
    
    /**
     * Get bank_account.
     *
     * @return string|null
     */
    public function getBankAccount()
    {
        return $this->bank_account;
    }

    /**
     * Get sales_amount.
     *
     * @return int
     */
    public function getSalesAmount()
    {
        return $this->sales_amount;
    }

    /**
     * Set sales_amount
     * 
     * @param int $sales_amount
     * 
     * @return Restaurant
     */
    public function setSalesAmount($sales_amount = null)
    {
        $this->sales_amount = $sales_amount;
        return $this;
    }

    /**
     * Get back_rate.
     *
     * @return int
     */
    public function getBackRate()
    {
        return $this->back_rate;
    }

    /**
     * Set back_rate
     * 
     * @param int $back_rate
     * 
     * @return Restaurant
     */
    public function setBackRate($back_rate = null)
    {
        $this->back_rate = $back_rate;
        return $this;
    }

    /**
     * Set pref.
     *
     * @param \Eccube\Entity\Master\Pref|null $pref
     *
     * @return Restaurant
     */
    public function setPref(\Eccube\Entity\Master\Pref $pref = null)
    {
        $this->Pref = $pref;
        return $this;
    }

    /**
     * Get pref.
     *
     * @return \Eccube\Entity\Master\Pref|null
     */
    public function getPref()
    {
        return $this->Pref;
    }

    /**
     * Add restaurantCategory.
     *
     * @param Plugin\restaurant_food\Entity\RestaurantCategory $restaurantCategory
     *
     * @return Restaurant
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

    /**
     * Add restaurantTag.
     *
     * @param Plugin\restaurant_food\Entity\RestaurantTag $restaurantTag
     *
     * @return Restaurant
     */
    public function addRestaurantTag(\Plugin\restaurant_food\Entity\RestaurantTag $restaurantTag)
    {
        $this->RestaurantTag[] = $restaurantTag;

        return $this;
    }

    /**
     * Remove restaurantTag.
     *
     * @param Plugin\restaurant_food\Entity\RestaurantTag $restaurantTag
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRestaurantTag(\Plugin\restaurant_food\Entity\RestaurantTag $restaurantTag)
    {
        return $this->RestaurantTag->removeElement($restaurantTag);
    }

    /**
     * Get restaurantTag.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantTag()
    {
        return $this->RestaurantTag;
    }

    /**
     * Add product.
     *
     * @param \Eccube\Entity\Product $product
     *
     * @return Restaurant
     */
    public function addProduct(\Eccube\Entity\Product $product)
    {
        $this->Products[] = $product;

        return $this;
    }

    /**
     * Remove product.
     *
     * @param \Eccube\Entity\Product $product
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeProduct(\Eccube\Entity\Product $product)
    {
        return $this->Products->removeElement($product);
    }

    /**
     * Get Products.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->Products;
    }

    /**
     * Add restaurantImage.
     *
     * @param \Eccube\Entity\ProductImage $restaurantImage
     *
     * @return Product
     */
    public function addRestaurantImage(\Eccube\Entity\ProductImage $restaurantImage)
    {
        $this->RestaurantImage[] = $restaurantImage;
        return $this;
    }

    /**
     * Remove restaurantImage.
     *
     * @param \Eccube\Entity\ProductImage $restaurantImage
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeRestaurantImage(\Eccube\Entity\ProductImage $restaurantImage)
    {
        return $this->RestaurantImage->removeElement($restaurantImage);
    }

    /**
     * Get restaurantImage.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantImage()
    {
        return $this->RestaurantImage;
    }

    /**
     * Set company_name.
     *
     * @param string $company_name
     *
     * @return Restaurant
     */
    public function setCompanyName($company_name)
    {
        $this->company_name = $company_name;

        return $this;
    }

    /**
     * Get company_name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return $this->company_name;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return Restaurant
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

        /**
     * Set content.
     *
     * @param string $content
     *
     * @return Restaurant
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
    
    /**
     * Set state.
     *
     * @param int $state
     *
     * @return Restaurant
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }
    
    /**
     * Set lower_price_limit.
     *
     * @param int $lower_price_limit
     *
     * @return Restaurant
     */
    public function setLowerPriceLimit($lower_price_limit)
    {
        $this->lower_price_limit = $lower_price_limit;

        return $this;
    }

    /**
     * Get lower_price_limit.
     *
     * @return int
     */
    public function getLowerPriceLimit()
    {
        return $this->lower_price_limit;
    }

    /**
     * Set deliverable_area.
     *
     * @param int $deliverable_area
     *
     * @return Restaurant
     */
    public function setDeliverableArea($deliverable_area)
    {
        $this->deliverable_area = $deliverable_area;

        return $this;
    }

    /**
     * Get deliverable_area.
     *
     * @return int
     */
    public function getDeliverableArea()
    {
        return $this->deliverable_area;
    }

    /**
     * Set food_type_list.
     *
     * @param int $food_type_list
     *
     * @return Restaurant
     */
    public function setFoodTypeList($food_type_list)
    {
        $this->food_type_list = $food_type_list;

        return $this;
    }

    /**
     * Get food_type_list.
     *
     * @return int
     */
    public function getFoodTypeList()
    {
        return $this->food_type_list;
    }

    /**
     * Set regist_code.
     *
     * @param string $regist_code
     *
     * @return Restaurant
     */
    public function setRegistCode($regist_code)
    {
        $this->regist_code = $regist_code;

        return $this;
    }

    /**
     * Get regist_code.
     *
     * @return string
     */
    public function getRegistCode()
    {
        return $this->regist_code;
    }

    /**
     * Set delivery_fees.
     *
     * @param string $delivery_fees
     *
     * @return Restaurant
     */
    public function setDeliveryFees($delivery_fees)
    {
        $this->delivery_fees = $delivery_fees;

        return $this;
    }

    /**
     * Get delivery_fees.
     *
     * @return string
     */
    public function getDeliveryFees()
    {
        return $this->delivery_fees;
    }

    /**
     * @param bool $reversedVat
     * @return Temp
     */
    public function setIsDeliverableOne($is_deliverable_one)
    {
        $this->is_deliverable_one = $is_deliverable_one;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDeliverableOne()
    {
        return $this->is_deliverable_one;
    }
}
