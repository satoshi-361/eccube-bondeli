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

namespace Eccube\Controller;

use Eccube\Entity\Page;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\Master\DeviceTypeRepository;
use Eccube\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Plugin\restaurant_food\Entity\Config as Restaurant;
use Plugin\restaurant_food\Form\Type\Admin\ConfigType as RestaurantType;
use Plugin\restaurant_food\Repository\ConfigRepository as RestaurantRepository;
use Eccube\Entity\Product;

class UserDataController extends AbstractController
{
	/**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;
	
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var DeviceTypeRepository
     */
    protected $deviceTypeRepository;

    /**
     * UserDataController constructor.
     *
     * @param PageRepository $pageRepository
     * @param DeviceTypeRepository $deviceTypeRepository
	 * @param RestaurantRepository $restaurantRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        DeviceTypeRepository $deviceTypeRepository,
		RestaurantRepository $restaurantRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->deviceTypeRepository = $deviceTypeRepository;
		$this->restaurantRepository = $restaurantRepository;
    }

    /**
     * @Route("/%eccube_user_data_route%/{route}", name="user_data", requirements={"route": "([0-9a-zA-Z_\-]+\/?)+(?<!\/)"})
     */
    public function index(Request $request, $route)
    {
        $Page = $this->pageRepository->findOneBy(
            [
                'url' => $route,
                'edit_type' => Page::EDIT_TYPE_USER,
            ]
        );

        if (null === $Page) {
            throw new NotFoundHttpException();
        }

        $file = sprintf('@user_data/%s.twig', $Page->getFileName());

        $event = new EventArgs(
            [
                'Page' => $Page,
                'file' => $file,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_USER_DATA_INDEX_INITIALIZE, $event);

        return $this->render($file);
    }
	

	
    /**
     * @Route("/restaurant/{id}", requirements={"id" = "\d+"}, name="restaurant")
     
    public function store(Request $request, $id = null)
    {
        $Restaurant = $this->restaurantRepository->find($id);
        $Products = $Restaurant->getProducts();

        $Page = $this->pageRepository->findOneBy(
            [
                'url' => 'store',
                'edit_type' => Page::EDIT_TYPE_USER,
            ]
        );

        if (null === $Page) {
            throw new NotFoundHttpException();
        }

        $file = sprintf('@user_data/%s.twig', 'store');

        $event = new EventArgs(
            [
                'Page' => $Page,
                'file' => $file,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_USER_DATA_INDEX_INITIALIZE, $event);

        return $this->render($file, [
            'Restaurant' => $Restaurant,
            'Products' => $Products
        ]);
    }*/
}
