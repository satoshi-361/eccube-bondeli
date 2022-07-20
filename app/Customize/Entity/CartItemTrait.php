<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\CartItem")
 */
trait CartItemTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="additional_option", type="string", nullable=true)
     */
    private $additional_option;

    /**
     * @return string
     */
    public function getAdditionalOption()
    {
        return $this->additional_option;
    }

    /**
     * @param  string  $additional_option
     *
     * @return CartItem
     */
    public function setAdditionalOption($additional_option)
    {
        $this->additional_option = $additional_option;
        
        return $this;
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="additional_price", type="integer", nullable=true)
     */
    private $additional_price = 0;

    /**
     * @return int
     */
    public function getAdditionalPrice()
    {
        return $this->additional_price;
    }

    /**
     * @param  integer  $additional_price
     *
     * @return CartItem
     */
    public function setAdditionalPrice($additional_price)
    {
        $this->additional_price = $additional_price;
        
        return $this;
    }
}