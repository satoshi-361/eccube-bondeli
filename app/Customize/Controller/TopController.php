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

namespace Customize\Controller;

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
use Eccube\Controller\AbstractController;

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
        if ( $date != '' || $location != '' || $type != 0 ) {
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

                if ($type != 0) {
                    $i = 0;
                    $break = false;
                    foreach($Restaurant->getRestaurantCategories() as $i => $RestaurantCategory) {
                        if ($RestaurantCategory->getCategory()->getId() == $type) {
                            $break = true;
                            break;
                        }
                    }
                    if ( !$break ) {
                        array_push($removed_array, $key);
                        continue;
                    }
                }

                if ($location != '') {
                    $deliverable_area = explode(', ', $Restaurant->getDeliverableArea());
                    if (!in_array( $request->get('p-locality'), $deliverable_area )) {
                        array_push($removed_array, $key);
                    }
                }
            }
            foreach ($removed_array as $value)
                unset($Restaurants[$value]);
            
            $temp = [];
            foreach ($Restaurants as $key => $Restaurant) {
                $url = 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$location.',japan&destinations='.$Restaurant->getPostalCode().',japan&mode=driving&language=en-EN&sensor=false&key=AIzaSyARTz7O22IZDcHqU9_GwA0RA-tXpZjZPPw';
                $data   = @file_get_contents($url);
                $result = json_decode($data, true);
                $distance = $result["rows"][0]["elements"][0]["distance"]["value"] / 1000;
                
                $temp[$key]['distance'] = $distance;
                $temp[$key]['Restaurant'] = $Restaurant;
            }

            array_multisort(array_map(function($element) {
                return $element['distance'];
            }, $temp), SORT_ASC, $temp);

            $Restaurants = [];
            foreach($temp as $item) {
                $Restaurants[] = $item['Restaurant'];
            }
        }

        $TopCategories = $this->categoryRepository->getList(null);
        $Products = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return [
            'Restaurants' => $Restaurants,
            'TopCategories' => $TopCategories,
            'Products' => $Products,
            'date' => $date,
            'location' => $location,
            'type' => $type
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