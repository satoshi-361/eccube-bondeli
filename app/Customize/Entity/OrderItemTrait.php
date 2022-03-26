<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\OrderItem")
 */
trait OrderItemTrait
{
    /**
     * @var integer
     *
     * @ORM\Column(name="additional_price", type="integer")
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