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

namespace Plugin\restaurant_food\Controller\Admin;

use Carbon\Carbon;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\ProductStock;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\PluginApiException;
use Eccube\Form\Type\Admin\ChangePasswordType;
use Eccube\Form\Type\Admin\LoginType;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Eccube\Repository\MemberRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Service\PluginApiService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Plugin\restaurant_food\Entity\Config as Restaurant;
use Plugin\restaurant_food\Repository\ConfigRepository as RestaurantRepository;

class RestaurantController extends AbstractController
{
    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @var AuthenticationUtils
     */
    protected $helper;

    /**
     * @var MemberRepository
     */
    protected $memberRepository;

    /**
     * @var RestaurantRepository
     */
    protected $restaurantRepository;

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /** @var PluginApiService */
    protected $pluginApiService;

    /**
     * @var array 売り上げ状況用受注状況
     */
    private $excludes = [OrderStatus::CANCEL, OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::RETURNED];

    /**
     * AdminController constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param AuthenticationUtils $helper
     * @param MemberRepository $memberRepository
     * @param EncoderFactoryInterface $encoderFactory
     * @param OrderRepository $orderRepository
     * @param OrderStatusRepository $orderStatusRepository
     * @param CustomerRepository $custmerRepository
     * @param ProductRepository $productRepository
     * @param RestaurantRepository $restaurantRepository
     * @param PluginApiService $pluginApiService
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        AuthenticationUtils $helper,
        MemberRepository $memberRepository,
        EncoderFactoryInterface $encoderFactory,
        OrderRepository $orderRepository,
        OrderStatusRepository $orderStatusRepository,
        CustomerRepository $custmerRepository,
        ProductRepository $productRepository,
        RestaurantRepository $restaurantRepository,
        PluginApiService $pluginApiService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->helper = $helper;
        $this->memberRepository = $memberRepository;
        $this->encoderFactory = $encoderFactory;
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->customerRepository = $custmerRepository;
        $this->productRepository = $productRepository;
        $this->pluginApiService = $pluginApiService;
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * 管理画面ホーム
     *
     * @param Request $request
     *
     * @return array
     *
     * @throws NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @Route("/restaurant/{id}/admin", name="restaurant_homepage")
     * @Template("/Restaurant/index.twig")
     */
    public function index(Request $request, $id = null)
    {
        $request->getSession()->set('restaurant_id', $id);

        $adminRoute = $this->eccubeConfig['eccube_admin_route'];
        $is_danger_admin_url = false;
        if ($adminRoute === 'admin') {
            $is_danger_admin_url = true;
        }
        /**
         * 受注状況.
         */
        $excludes = [];
        $excludes[] = OrderStatus::CANCEL;
        $excludes[] = OrderStatus::DELIVERED;
        $excludes[] = OrderStatus::PENDING;
        $excludes[] = OrderStatus::PROCESSING;
        $excludes[] = OrderStatus::RETURNED;

        $event = new EventArgs(
            [
                'excludes' => $excludes,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ADMIM_INDEX_ORDER, $event);
        $excludes = $event->getArgument('excludes');

        // 受注ステータスごとの受注件数.
        $Orders = $this->getOrderEachStatus($excludes);

        // 受注ステータスの一覧.
        $Criteria = new Criteria();
        $Criteria
            ->where($Criteria::expr()->notIn('id', $excludes))
            ->orderBy(['sort_no' => 'ASC']);
        $OrderStatuses = $this->orderStatusRepository->matching($Criteria);

        /**
         * 売り上げ状況
         */
        $event = new EventArgs(
            [
                'excludes' => $this->excludes,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ADMIM_INDEX_SALES, $event);
        $this->excludes = $event->getArgument('excludes');

        $temp = $this->cstm_sale($request);

        /**
         * ショップ状況
         */
        // 在庫切れ商品数
        $countNonStockProducts = $this->countNonStockProducts();

        // 取り扱い商品数
        $countProducts = $this->countProducts($id);

        // 本会員数
        $countCustomers = $this->countCustomers();

        $event = new EventArgs(
            [
                'Orders' => $Orders,
                'OrderStatuses' => $OrderStatuses,
                'salesThisMonth' => $temp[2],
                'salesToday' => $temp[0],
                'salesYesterday' => $temp[1],
                'countNonStockProducts' => $countNonStockProducts,
                'countProducts' => $countProducts,
                'countCustomers' => $countCustomers,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ADMIM_INDEX_COMPLETE, $event);

        // 推奨プラグイン
        $recommendedPlugins = [];
        try {
            $recommendedPlugins = $this->pluginApiService->getRecommended();
        } catch (PluginApiException $ignore) {
        }

        return [
            'Orders' => $Orders,
            'OrderStatuses' => $OrderStatuses,
            'salesThisMonth' => $temp[2],
            'salesToday' => $temp[0],
            'salesYesterday' => $temp[1],
            'countNonStockProducts' => $countNonStockProducts,
            'countProducts' => $countProducts,
            'countCustomers' => $countCustomers,
            'recommendedPlugins' => $recommendedPlugins,
            'is_danger_admin_url' => $is_danger_admin_url,
            'Restaurant_id' => $id
        ];
    }

    /**
     * 売上状況の取得
     *
     * @param Request $request
     *
     * @Route("/restaurant/{id}/sale_chart", name="restaurant_homepage_sale")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sale(Request $request)
    {
        if (!($request->isXmlHttpRequest() && $this->isTokenValid())) {
            return $this->json(['status' => 'NG'], 400);
        }

        // 週間の売上金額
        $toDate = Carbon::now();
        $fromDate = Carbon::today()->subWeek();
        $rawWeekly = $this->getData($request, $fromDate, $toDate, 'Y/m/d');

        // 月間の売上金額
        $fromDate = Carbon::now()->startOfMonth();
        $rawMonthly = $this->getData($request, $fromDate, $toDate, 'Y/m/d');

        // 年間の売上金額
        $fromDate = Carbon::now()->subYear()->startOfMonth();
        $rawYear = $this->getData($request, $fromDate, $toDate, 'Y/m');

        $datas = [$rawWeekly, $rawMonthly, $rawYear];

        return $this->json($datas);
    }

    /**
     * 在庫なし商品の検索結果を表示する.
     *
     * @Route("/restaurant/{id}/search_nonstock", name="restaurant_homepage_nonstock")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchNonStockProducts(Request $request)
    {
        // 在庫なし商品の検索条件をセッションに付与し, 商品マスタへリダイレクトする.
        $searchData = [];
        $searchData['stock'] = [ProductStock::OUT_OF_STOCK];
        $session = $request->getSession();
        $session->set('eccube.admin.product.search', $searchData);

        return $this->redirectToRoute('admin_product_page', [
            'page_no' => 1,
        ]);
    }

    /**
     * 本会員の検索結果を表示する.
     *
     * @Route("/restaurant/{id}/search_customer", name="restaurant_homepage_customer")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchCustomer(Request $request)
    {
        $searchData = [];
        $searchData['customer_status'] = [CustomerStatus::REGULAR];
        $session = $request->getSession();
        $session->set('eccube.admin.customer.search', $searchData);

        return $this->redirectToRoute('admin_customer_page', [
            'page_no' => 1,
        ]);
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param array $excludes
     *
     * @return null|Request
     */
    protected function getOrderEachStatus(array $excludes)
    {
        $sql = 'SELECT
                    t1.order_status_id as status,
                    COUNT(t1.id) as count
                FROM
                    dtb_order t1
                WHERE
                    t1.order_status_id NOT IN (:excludes)
                GROUP BY
                    t1.order_status_id
                ORDER BY
                    t1.order_status_id';
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('status', 'status');
        $rsm->addScalarResult('count', 'count');
        $query = $this->entityManager->createNativeQuery($sql, $rsm);
        $query->setParameters([':excludes' => $excludes]);
        $result = $query->getResult();
        $orderArray = [];
        foreach ($result as $row) {
            $orderArray[$row['status']] = $row['count'];
        }

        return $orderArray;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return array|mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getSalesByDay(Request $request, $dateTime)
    {
        $dateTimeStart = clone $dateTime;
        $dateTimeStart->setTime(0, 0, 0, 0);

        $dateTimeEnd = clone $dateTimeStart;
        $dateTimeEnd->modify('+1 days');

        $qb = $this->orderRepository
            ->createQueryBuilder('o')
            ->select('
            SUM(o.payment_total) AS order_amount,
            COUNT(o) AS order_count')
            ->setParameter(':excludes', $this->excludes)
            ->setParameter(':targetDateStart', $dateTimeStart)
            ->setParameter(':targetDateEnd', $dateTimeEnd)
            ->andWhere(':targetDateStart <= o.order_date and o.order_date < :targetDateEnd')
            ->andWhere('o.OrderStatus NOT IN (:excludes)');
        $q = $qb->getQuery();

        $result = [];

        try {
            $result = $q->getSingleResult();
        } catch (NoResultException $e) {
            // 結果がない場合は空の配列を返す.
        }

        return $result;
    }

    /**
     * @param \DateTime $dateTime
     *
     * @return array|mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getSalesByMonth(Request $request, $dateTime)
    {
        $dateTimeStart = clone $dateTime;
        $dateTimeStart->setTime(0, 0, 0, 0);
        $dateTimeStart->modify('first day of this month');

        $dateTimeEnd = clone $dateTime;
        $dateTimeEnd->setTime(0, 0, 0, 0);
        $dateTimeEnd->modify('first day of 1 month');

        $qb = $this->orderRepository
            ->createQueryBuilder('o')
            ->select('
            SUM(o.payment_total) AS order_amount,
            COUNT(o) AS order_count')
            ->setParameter(':excludes', $this->excludes)
            ->setParameter(':targetDateStart', $dateTimeStart)
            ->setParameter(':targetDateEnd', $dateTimeEnd)
            ->andWhere(':targetDateStart <= o.order_date and o.order_date < :targetDateEnd')
            ->andWhere('o.OrderStatus NOT IN (:excludes)');
        $q = $qb->getQuery();

        $result = [];
        // $restaurant_id = $request->getSession()->get('restaurant_id');

        try {
            $result = $q->getSingleResult();

            // foreach($result->getOrderItems() as $OrderItem) {
            //     if ($OrderItem->getProduct() == null) {
            //         $result->removeOrderItem($OrderItem);
            //         continue;
            //     }
            //     if ($OrderItem->getProduct()->getRestaurant()->getId() != $restaurant_id) {
            //         $result->removeOrderItem($OrderItem);
            //     }
            // }

        } catch (NoResultException $e) {
            // 結果がない場合は空の配列を返す.
        }

        return $result;
    }

    /**
     * 在庫切れ商品数を取得
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function countNonStockProducts()
    {
        $qb = $this->productRepository->createQueryBuilder('p')
            ->select('count(DISTINCT p.id)')
            ->innerJoin('p.ProductClasses', 'pc')
            ->where('pc.stock_unlimited = :StockUnlimited AND pc.stock = 0')
            ->andWhere('pc.visible = :visible')
            ->setParameter('StockUnlimited', false)
            ->setParameter('visible', true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 商品数を取得
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function countProducts($res_id)
    {
        $qb = $this->productRepository->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.Status in (:Status)')
            ->andWhere('p.Restaurant = :Restaurant')
            ->setParameter('Status', [ProductStatus::DISPLAY_SHOW, ProductStatus::DISPLAY_HIDE])
            ->setParameter('Restaurant', $this->restaurantRepository->find($res_id));

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 本会員数を取得
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function countCustomers()
    {
        $qb = $this->customerRepository->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.Status = :Status')
            ->setParameter('Status', CustomerStatus::REGULAR);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 期間指定のデータを取得
     *
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param $format
     *
     * @return array
     */
    protected function getData(Request $request, Carbon $fromDate, Carbon $toDate, $format)
    {
        $qb = $this->orderRepository->createQueryBuilder('o')
            ->andWhere('o.order_date >= :fromDate')
            ->andWhere('o.order_date <= :toDate')
            ->andWhere('o.OrderStatus NOT IN (:excludes)')
            ->setParameter(':excludes', $this->excludes)
            ->setParameter(':fromDate', $fromDate->copy())
            ->setParameter(':toDate', $toDate->copy())
            ->orderBy('o.order_date');

        $result = $qb->getQuery()->getResult();

        $Orders = $result;
        $unset_keys = array();
        $restaurant_id = $request->getSession()->get('restaurant_id');

        foreach($Orders as $key => $Order) {
            foreach($Order->getOrderItems() as $OrderItem) {
                if ($OrderItem->getProduct() == null) {
                    $Order->removeOrderItem($OrderItem);
                    continue;
                }
                if ($OrderItem->getProduct()->getRestaurant()->getId() != $restaurant_id) {
                    $Order->removeOrderItem($OrderItem);
                }
            }
            if (count($Order->getOrderItems()) == 0)
                array_push($unset_keys, $key);
        }
        foreach($unset_keys as $key)
            unset($Orders[$key]);

        return $this->convert($Orders, $fromDate, $toDate, $format);
    }

    /**
     * 期間毎にデータをまとめる
     *
     * @param $result
     * @param Carbon $fromDate
     * @param Carbon $toDate
     * @param $format
     *
     * @return array
     */
    protected function convert($result, Carbon $fromDate, Carbon $toDate, $format)
    {
        // $response = [];

        // if ($format == 'sales') {
        //     $order_amount = 0;

        //     foreach($result as $Order) {
        //         $order_amount = $Order->getPaymentTotal();
        //     }
        //     $response['order_amount'] = $order_amount;
        //     $response['order_count'] = count($result);

        //     return $response;
        // }


        $raw = [];
        for ($date = $fromDate; $date <= $toDate; $date = $date->addDay()) {
            $raw[$date->format($format)]['price'] = 0;
            $raw[$date->format($format)]['count'] = 0;
        }

        foreach ($result as $Order) {
            $raw[$Order->getOrderDate()->format($format)]['price'] += $Order->getPaymentTotal();
            ++$raw[$Order->getOrderDate()->format($format)]['count'];
        }

        return $raw;
    }

    public function cstm_sale(Request $request)
    {
        // 週間の売上金額
        $toDate = Carbon::now();
        $fromDate = Carbon::today()->subWeek();
        $rawWeekly = $this->getData($request, $fromDate, $toDate, 'Y/m/d');

        // 月間の売上金額
        $fromDate = Carbon::now()->startOfMonth();
        $rawMonthly = $this->getData($request, $fromDate, $toDate, 'Y/m/d');
        
        $salesToday = [];
        $salesYesterday = [];
        $salesThisMonth = [];

        $temp = [];

        foreach ($rawWeekly as $value) {
            # code...
            array_push($temp, $value);
        }
        
        $salesToday['order_amount'] = $temp[7]['price'];
        $salesToday['order_count'] = $temp[7]['count'];
        
        $salesYesterday['order_amount'] = $temp[6]['price'];
        $salesYesterday['order_count'] = $temp[6]['count'];

        $temp = [];

        $salesThisMonth['order_amount'] = 0;
        $salesThisMonth['order_count'] = 0;

        foreach ($rawMonthly as $value) {
            # code...
            array_push($temp, $value);

            $salesThisMonth['order_amount'] += $value['price'];
            $salesThisMonth['order_count'] += $value['count'];
        }

        $result = [];

        array_push($result, $salesToday);
        array_push($result, $salesYesterday);
        array_push($result, $salesThisMonth);

        return $result;
    }
}
