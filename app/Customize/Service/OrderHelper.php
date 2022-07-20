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

namespace Customize\Service;

use Eccube\Service\OrderHelper as BaseOrderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Customer;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Master\OrderItemType;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Entity\Shipping;
use Eccube\EventListener\SecurityListener;
use Eccube\Repository\DeliveryRepository;
use Eccube\Repository\Master\DeviceTypeRepository;
use Eccube\Repository\Master\OrderItemTypeRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\Master\PrefRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\PaymentRepository;
use Eccube\Util\StringUtil;
use SunCat\MobileDetectBundle\DeviceDetector\MobileDetector;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderHelper extends BaseOrderHelper
{
    /**
     * @param Collection|ArrayCollection|CartItem[] $CartItems
     *
     * @return OrderItem[]
     */
    protected function createOrderItemsFromCartItems($CartItems)
    {
        $ProductItemType = $this->orderItemTypeRepository->find(OrderItemType::PRODUCT);

        $OrderItems = array_map(function ($item) use ($ProductItemType) {
            /* @var $item CartItem */
            /* @var $ProductClass \Eccube\Entity\ProductClass */
            $ProductClass = $item->getProductClass();
            /* @var $Product \Eccube\Entity\Product */
            $Product = $ProductClass->getProduct();
            $OrderItem = new OrderItem();

            $additional_price = ($item->getPriceIncTax() - $ProductClass->getPrice02() * 1.1) / 1.1;
            $OrderItem
                ->setProduct($Product)
                ->setProductClass($ProductClass)
                ->setProductName($Product->getName())
                ->setProductCode($ProductClass->getCode())
                ->setPrice($ProductClass->getPrice02())
                ->setQuantity($item->getQuantity())
                ->setOrderItemType($ProductItemType)
                ->setAdditionalOption($item->getAdditionalOption());

            $ClassCategory1 = $ProductClass->getClassCategory1();
            if (!is_null($ClassCategory1)) {
                $OrderItem->setClasscategoryName1($ClassCategory1->getName());
                $OrderItem->setClassName1($ClassCategory1->getClassName()->getName());
            }
            $ClassCategory2 = $ProductClass->getClassCategory2();
            if (!is_null($ClassCategory2)) {
                $OrderItem->setClasscategoryName2($ClassCategory2->getName());
                $OrderItem->setClassName2($ClassCategory2->getClassName()->getName());
            }

            return $OrderItem;
        }, $CartItems instanceof Collection ? $CartItems->toArray() : $CartItems);

        $AdditionalOptionItemType = $this->orderItemTypeRepository->find(OrderItemType::CHARGE);

        $AdditionalOptions = array_map(function ($item) use ($AdditionalOptionItemType) {
            if ($item->getAdditionalPrice()) { 
                /* @var $item CartItem */
                /* @var $ProductClass \Eccube\Entity\ProductClass */
                $ProductClass = $item->getProductClass();
                /* @var $Product \Eccube\Entity\Product */
                $Product = $ProductClass->getProduct();

                $OrderItem = new OrderItem();
                $OrderItem
                    ->setProduct($Product)
                    ->setProductClass($ProductClass)
                    ->setPrice($item->getAdditionalPrice())
                    ->setProductName($item->getAdditionalOption())
                    ->setOrderItemType($AdditionalOptionItemType)
                    ->setQuantity($item->getQuantity())
                    ->setAdditionalOption($item->getAdditionalOption());

                return $OrderItem;
            } 
        }, $CartItems instanceof Collection ? $CartItems->toArray() : $CartItems);
    
        foreach($AdditionalOptions as $Item) {
            if (!is_null($Item)) array_push($OrderItems, $Item);
        }

        return $OrderItems;
    }
}
