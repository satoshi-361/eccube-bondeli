<?php

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
  * @EntityExtension("Eccube\Entity\Order")
 */
trait OrderTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="sq_payment_id", type="string", nullable=true)
     */
    private $sq_payment_id = '';

    /**
     * @var string
     *
     * @ORM\Column(name="reject_message", type="string", nullable=true)
     */
    private $reject_message = '';

    /**
     * @var string
     *
     * @ORM\Column(name="accept_url", type="string", nullable=true)
     */
    private $accept_url = '';

    /**
     * @var string
     *
     * @ORM\Column(name="reject_url", type="string", nullable=true)
     */
    private $reject_url = '';

    /**
     * @return string
     */
    public function getSqPaymentId()
    {
        return $this->sq_payment_id;
    }

    /**
     * @param  string  $sq_payment_id
     *
     * @return Order
     */
    public function setSqPaymentId($sq_payment_id)
    {
        $this->sq_payment_id = $sq_payment_id;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getRejectMessage()
    {
        return $this->reject_message;
    }

    /**
     * @param  string  $reject_message
     *
     * @return Order
     */
    public function setRejectMessage($reject_message)
    {
        $this->reject_message = $reject_message;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getAcceptUrl()
    {
        return $this->accept_url;
    }

    /**
     * @param  string  $accept_url
     *
     * @return Order
     */
    public function setAcceptUrl($accept_url)
    {
        $this->accept_url = $accept_url;
        
        return $this;
    }
    /**
     * @return string
     */
    public function getRejectUrl()
    {
        return $this->reject_url;
    }

    /**
     * @param  string  $reject_url
     *
     * @return Order
     */
    public function setRejectUrl($reject_url)
    {
        $this->reject_url = $reject_url;
        
        return $this;
    }
}