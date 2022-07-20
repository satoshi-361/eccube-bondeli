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
     * @var string
     *
     * @ORM\Column(name="additional_option", type="string", nullable=true)
     */
    private $additional_option = 0;

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
}