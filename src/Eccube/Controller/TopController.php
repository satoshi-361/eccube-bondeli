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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\RouterInterface;

use Plugin\restaurant_food\Entity\Config as Restaurant;
use Plugin\restaurant_food\Form\Type\Admin\ConfigType as RestaurantType;
use Plugin\restaurant_food\Repository\ConfigRepository as RestaurantRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Entity\Product;
use Eccube\Repository\CategoryRepository;

class TopController extends AbstractController
{
    /**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * TopController constructor
     * 
     * @param RestaurantRepository $restaurantRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct( 
        RestaurantRepository $restaurantRepository, 
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository )
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @Route("/", name="homepage")
     * @Route("/restaurant/search/search", name="restaurant_search")
     * @Template("index.twig")
     */
    public function index(Request $request)
    {
        $date = $request->get('date');
        $location = $request->get('location');
        $type = $request->get('type');

        $Restaurants = $this->restaurantRepository->findAll();
        $removed_array = array();

        if ( isset($date) || isset($location) || isset($type) ) {
            $search_year = intval(substr($date, 0, 4));
            $search_month = intval(substr($date, 7, 2));
            $search_day = intval(substr($date, 12, 2));

            $search_date = new \DateTime();
            $search_date->setDate($search_year, $search_month, $search_day);
            $interval = date_diff($search_date, new \Datetime());

            $itv_year = $interval->format('%Y');
            $itv_month = $interval->format('%m');
            $itv_date = $interval->format('%d');

            foreach ($Restaurants as $key => $Restaurant) {
                if ($date != '') {
                    if ($itv_date < $Restaurant->getDeliverable()){
                        array_push($removed_array, $key);
                        continue;
                    }          
                } 
                $addr = $Restaurant->getAddr01();
                if ( strstr($addr, $location) == false ) {
                    array_push($removed_array, $key);
                    continue;
                }
                foreach($Restaurant->getRestaurantCategories() as $i => $RestaurantCategory) {
                    if ($RestaurantCategory->getCategory()->getId() == $type) break;
                }
                if ( $i == count($Restaurant->getRestaurantCategories()) )
                    array_push($removed_array, $key);
            }
            foreach ($removed_array as $value)
                unset($Restaurants[$value]);
        }

        $TopCategories = $this->categoryRepository->getList(null);
        $Products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return [
            'Restaurants' => $Restaurants,
            'TopCategories' => $TopCategories,
            'Products' => $Products
        ];
    }
	
	/**
     * @Route("/restaurant/{id}", name="restaurant")
     * @Template("store.twig")
     */
    public function store(Request $request, $id = null)
    {
        $Restaurant = $this->restaurantRepository->find($id);

        return [
            'Restaurant' => $Restaurant
        ];
    }

    /**
     * @Route("/driver_recurit", name="driver_recurit")
     * @Template("driver_recurit.twig")
     */
    public function driverRecurit(Request $request)
    {
        return [];
    }
}