<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Customer")
 */
trait CustomerTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="nickname", type="string", nullable=true)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card", type="string", nullable=true)
     */
    private $credit_card;

    /**
     * set nickname
     * 
     * @param string|null $nickname
     * 
     * @return Customer
     */
    public function setNickName($nickname)
    {
        $this->nickname = $nickname;
        return $this;
    }

    /**
     * Get nickname 
     * 
     * @return string|null
     */
    public function getNickName()
    {
        return $this->nickname;
    }

    /**
     * set credit_card
     * 
     * @param string|null $credit_card
     * 
     * @return Customer
     */
    public function setCreditCard($credit_card)
    {
        $this->credit_card = $credit_card;
        return $this;
    }

    /**
     * Get credit_card
     * 
     * @return string|null
     */
    public function getCreditCard()
    {
        return $this->credit_card;
    }
}