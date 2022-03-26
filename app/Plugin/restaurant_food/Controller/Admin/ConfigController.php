<?php

namespace Plugin\restaurant_food\Controller\Admin;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Controller\AbstractController;
use Eccube\Repository\TagRepository;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductImageRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\OrderRepository;
use Eccube\Repository\Master\OrderStatusRepository;
use Plugin\restaurant_food\Entity\Config as Restaurant;
use Plugin\restaurant_food\Form\Type\Admin\ConfigType as ConfigType;
use Plugin\restaurant_food\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\RouterInterface;
use Knp\Component\Pager\Paginator;
use Eccube\Event\EventArgs;
use Eccube\Event\EccubeEvents;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Order;
use Eccube\Entity\OrderItem;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Form\Type\Front\CustomerLoginType;
use Eccube\Form\Type\AddCartType;
use Eccube\Form\Type\Admin\OrderType;
use Eccube\Form\Type\Admin\SearchCustomerType;
use Eccube\Form\Type\Admin\SearchProductType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseException;
use Eccube\Service\PurchaseFlow\PurchaseFlow;

use Plugin\restaurant_food\Entity\RestaurantCategory;
use Eccube\Repository\CustomerRepository;
use Eccube\Repository\DeliveryRepository;
use Eccube\Entity\Product;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Master\OrderStatus;
use Eccube\Entity\ProductStock;
use Eccube\Entity\ProductClass;
use Eccube\Entity\Tag;
use Eccube\Entity\ProductTag;
use Eccube\Repository\Master\ProductStatusRepository;
use Eccube\Repository\Master\SaleTypeRepository;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Eccube\Entity\Master\CsvType;
use Eccube\Service\CsvExportService;
use Eccube\Entity\ExportCsvRow;

class ConfigController extends AbstractController
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var CsvExportService
     */
    protected $csvExportService;

    /**
     * @var SaleTypeRepository
     */
    protected $saleTypeRepository;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var TagRepository
     */
    protected $tagRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;
    
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @var ProductImageRepository
     */
    protected $productImageRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * @var OrderStatusRepository
     */
    protected $orderStatusRepository;
    
    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     * @param ProductRepository $productRepository
     * @param CsvExportService $csvExportService
     * @param CategoryRepository $categoryRepository
     * @param DeliveryRepository $deliveryRepository
     * @param TagRepository $tagRepository
     * @param ProductImageRepository $productImageRepository
     * @param ProductStatusRepository $productStatusRepository
     * @param OrderStatusRepository $orderStatusRepository
     * @param SaleTypeRepository $saleTypeRepository
     * @param OrderRepository $orderRepository
     * @param SerializerInterface $serializer
     * @param \Swift_Mailer $mailer
     */
    public function __construct(
        \Swift_Mailer $mailer,
        ConfigRepository $configRepository,
        ProductRepository $productRepository,
        CsvExportService $csvExportService,
        TagRepository $tagRepository,
        DeliveryRepository $deliveryRepository,
        CategoryRepository $categoryRepository,
        ProductImageRepository $productImageRepository,
        SaleTypeRepository $saleTypeRepository,
        BaseInfoRepository $baseInfoRepository,
        OrderRepository $orderRepository,
        OrderStatusRepository $orderStatusRepository,
        SerializerInterface $serializer,
        ProductStatusRepository $productStatusRepository
    ) {
        $this->mailer = $mailer;
        $this->saleTypeRepository = $saleTypeRepository;
        $this->configRepository = $configRepository;
        $this->csvExportService = $csvExportService;
        $this->productImageRepository = $productImageRepository;
        $this->categoryRepository = $categoryRepository;
        $this->serializer = $serializer;
        $this->deliveryRepository = $deliveryRepository;
        $this->tagRepository = $tagRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->productStatusRepository = $productStatusRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/restaurant", name="restaurant_admin")
     * @Route("/%eccube_admin_route%/restaurant/delete/{del_no}", name="restaurant_del")
     * @Route("/%eccube_admin_route%/restaurant/permit/{permit_no}", name="restaurant_permit")
     * @Route("/%eccube_admin_route%/restaurant/page/{page_no}", requirements={"page_no" = "\d+"}, name="restaurant_admin_page")
     * @Template("@restaurant_food/admin/index.twig")
     */
    public function index(Request $request, $page_no = null, $del_no = null, $permit_no = null, Paginator $paginator)
    {
        if ($del_no != null) {
            $Restaurant = $this->configRepository->find($del_no);
            $this->entityManager->remove($Restaurant);

            try {
                $this->entityManager->flush();
            } catch (\Exception $ex) {
                log_warning('URLの形式が不正です。');
                echo $ex->getMessage();
            }
        }
        
        if ($permit_no != null) {
            $Restaurant = $this->configRepository->find($permit_no);
            $permit_code = md5($Restaurant->getCompanyName().$Restaurant->getEmail());
            $Restaurant->setRegistCode($permit_code);
            $Restaurant->setState(2);

            $message = (new \Swift_Message())
            ->setSubject('['.$this->BaseInfo->getShopName().'] ')
            ->setFrom([$this->BaseInfo->getEmail02() => $this->BaseInfo->getShopName()])
            ->setTo([$Restaurant->getEmail()])
            ->setBcc($this->BaseInfo->getEmail02())
            ->setReplyTo($this->BaseInfo->getEmail02())
            ->setReturnPath($this->BaseInfo->getEmail04());

            $message
                ->setContentType('text/plain; charset=UTF-8')
                ->setBody($permit_code, 'text/plain');
            
            $this->mailer->send($message);

            $this->entityManager->persist($Restaurant);
            $this->entityManager->flush();
        }

        $qb = $this->configRepository->findAll();

        $page_count = 20;
        if ($page_no == null) $page_no = 1;
        $pagination = $paginator->paginate(
            $qb,
            $page_no,
            $page_count
        );
        
        $restaurant_total_price = [];
        $Orders = $this->orderRepository->findAll();

        foreach($qb as $Restaurant) {
            $restaurant_total_price[$Restaurant->getId()] = 0;
        }

        // 月間の売上金額
        $fromDate = Carbon::now()->startOfMonth();
        $toDate = Carbon::now();

        $excludes = [OrderStatus::CANCEL, OrderStatus::PENDING, OrderStatus::PROCESSING, OrderStatus::RETURNED];

        $qb = $this->orderRepository->createQueryBuilder('o')
        ->andWhere('o.order_date >= :fromDate')
        ->andWhere('o.order_date <= :toDate')
        ->andWhere('o.OrderStatus NOT IN (:excludes)')
        ->setParameter(':excludes', $excludes)
        ->setParameter(':fromDate', $fromDate->copy())
        ->setParameter(':toDate', $toDate->copy())
        ->orderBy('o.order_date');

        $Orders = $qb->getQuery()->getResult();

        foreach($Orders as $Order) {
        foreach($Order->getMergedProductOrderItems() as $OrderItem) {
                $restaurant_total_price[$OrderItem->getProduct()->getRestaurant()->getId()] += $Order->getTotalPrice();
            }
        }

        return [
            'pagination' => $pagination,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'restaurant_total_price' => $restaurant_total_price
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/restaurant/new", name="restaurant_new")
     * @Route("/%eccube_admin_route%/restaurant/{id}/edit", requirements={"id" = "\d+"}, name="restaurant_edit")
     * @Template("@restaurant_food/admin/new.twig")
     */
    public function edit(Request $request, $id = null)
    {
        if (is_null($id)) {
            $Restaurant = new Restaurant();
        } else {
            $Restaurant = $this->configRepository->find($id);
            if (!$Restaurant) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(ConfigType::class, $Restaurant);

        $Products = $Restaurant->getProducts();

        $form['products']->setData($Products);

        $images = [];
        $RestaurantImages = $Restaurant->getRestaurantImage();
        foreach ($RestaurantImages as $RestaurantImage) {
            $images[] = $RestaurantImage->getFileName();
        }            
        // $restaurant_image_count = count($images);
        // if (isset($images[$restaurant_image_count - 1]))
        //     $form['images']->setData(array($images[$restaurant_image_count - 1]));
        $form['images']->setData($images);

        $categories = [];
        $RestaurantCategories = $Restaurant->getRestaurantCategories();
        foreach ($RestaurantCategories as $RestaurantCategory) {
            $categories[] = $RestaurantCategory->getCategory();
        }
        $form['Category']->setData($categories);

        $Tags = $Restaurant->getRestaurantTag();
        $form['Tag']->setData($Tags);

        $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            // if (!$form->isValid()) { print_r($this->getErrorMessages($form)); exit; }
            if (!$form->isValid());
            
            $Restaurant = $form->getData();

            // カテゴリの登録
            // 一度クリア
            /* @var $Restaurant Plugin\restaurant_food\Entity\Restaurant */
            foreach ($Restaurant->getRestaurantCategories() as $RestaurantCategory) {
                $Restaurant->removeRestaurantCategory($RestaurantCategory);
                $this->entityManager->remove($RestaurantCategory);
            }

            $Restaurant->setState(3);
            $this->entityManager->persist($Restaurant);
            $this->entityManager->flush();

            $count = 1;
            $Categories = $form->get('Category')->getData();
            
            $categoriesIdList = [];
            foreach ($Categories as $Category) {
                foreach ($Category->getPath() as $ParentCategory) {
                    if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                        $RestaurantCategory = $this->createRestaurantCategory($Restaurant, $ParentCategory, $count);
                        $this->entityManager->persist($RestaurantCategory);
                        $count++;
                        /* @var $Restaurant \Eccube\Entity\Restaurant */
                        $Restaurant->addRestaurantCategory($RestaurantCategory);
                        $categoriesIdList[$ParentCategory->getId()] = true;
                    }
                }
                if (!isset($categoriesIdList[$Category->getId()])) {
                    $RestaurantCategory = $this->createRestaurantCategory($Restaurant, $Category, $count);
                    $this->entityManager->persist($RestaurantCategory);
                    $count++;
                    /* @var $Restaurant \Eccube\Entity\Restaurant */
                    $Restaurant->addRestaurantCategory($RestaurantCategory);
                    $categoriesIdList[$ParentCategory->getId()] = true;
                }
            }

            $RestaurantTags = $Restaurant->getRestaurantTag();
            foreach ($RestaurantTags as $RestaurantTag) {
                $Restaurant->removeRestaurantTag($RestaurantTag);
                $this->entityManager->remove($RestaurantTag);
            }

            // 商品タグの登録
            $Tags = $form->get('Tag')->getData();
            foreach ($Tags as $Tag) {
                $RestaurantTag = new RestaurantTag();
                $RestaurantTag
                    ->setRestaurant($Restaurant)
                    ->setTag($Tag);
                $Restaurant->addRestaurantTag($RestaurantTag);
                $this->entityManager->persist($RestaurantTag);
            }
            $this->entityManager->flush();
            $event = new EventArgs(
                [
                    'form' => $form,
                    'Restaurant' => $Restaurant,
                ],
                $request
            );
            
            // 画像の登録
            $add_images = $form->get('add_images')->getData();
            foreach ($add_images as $add_image) {
                $RestaurantImage = new \Eccube\Entity\ProductImage();
                $RestaurantImage
                    ->setFileName($add_image)
                    ->setRestaurant($Restaurant)
                    ->setSortNo(1);
                $Restaurant->addRestaurantImage($RestaurantImage);
                $this->entityManager->persist($RestaurantImage);

                // 移動
                if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image)) {
                    $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image);
                    $file->move($this->eccubeConfig['eccube_save_image_dir']);
                }
            }

            // 画像の削除
            $delete_images = $form->get('delete_images')->getData();
            foreach ($delete_images as $delete_image) {
                $RestaurantImage = $this->productImageRepository
                    ->findOneBy(['file_name' => $delete_image]);

                // 追加してすぐに削除した画像は、Entityに追加されない
                if (get_class($RestaurantImage) == 'Eccube\Entity\ProductImage') {
                    $Restaurant->removeRestaurantImage($RestaurantImage);
                    $this->entityManager->remove($RestaurantImage);
                }
                $this->entityManager->persist($Restaurant);

                // 削除
                $fs = new Filesystem();
                $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
            }

            if (array_key_exists('products', $request->get('config'))) {
                $form_products = $request->get('config')['products'];
                $form_keys = array();
                foreach($form_products as $form_product) {
                    if (intval($form_product['id']) != 0) array_push($form_keys, intval($form_product['id']));
                }

                $Products = $Restaurant->getProducts();

                foreach($Products as $Product) {
                    if(in_array($Product->getId(), $form_keys)) continue;

                    $Product->setRestaurant(null);
                    $Restaurant->removeProduct($Product);

                    $this->entityManager->remove($Product);
                }

                try {
                    $this->entityManager->persist($Restaurant);
                    $this->entityManager->flush();
                } catch (\Exception $ex) {
                    log_warning('URLの形式が不正です。');
                    echo $ex->getMessage();
                }

                foreach ($form_products as $item) {
                    if ($item['id'] == 0) {
                        $Product = new Product();
                        $ProductClass = new ProductClass();
                        $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);
                        $Product
                            ->addProductClass($ProductClass)
                            ->setStatus($ProductStatus);
        
                        $saleType = $this->saleTypeRepository->find(1);
                        $ProductClass
                            ->setVisible(true)
                            ->setStockUnlimited(true)
                            ->setProduct($Product)
                            ->setPrice02($item['visible_price'])
                            ->setPrice01($item['visible_price'])
                            ->setSaleType($saleType);
                            
                        $this->entityManager->persist($ProductClass);
        
                        $ProductStock = new ProductStock();
                        $ProductStock->setStock(null);
                        $ProductClass->setProductStock($ProductStock);
                        $ProductStock->setProductClass($ProductClass);

                        $this->entityManager->persist($ProductStock);
                    } else {
                        $Product = $this->productRepository->find($item['id']);
                        $ProductClass = $Product->getProductClasses()[0];
                        $ProductClass
                            ->setPrice02($item['visible_price'])
                            ->setPrice01($item['visible_price']);
                    }
    
    
                    // 個別消費税
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if ($ProductClass->getTaxRate() !== null) {
                            if ($ProductClass->getTaxRule()) {
                                $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                            } else {
                                $taxrule = $this->taxRuleRepository->newTaxRule();
                                $taxrule->setTaxRate($ProductClass->getTaxRate());
                                $taxrule->setApplyDate(new \DateTime());
                                $taxrule->setProduct($Product);
                                $taxrule->setProductClass($ProductClass);
                                $ProductClass->setTaxRule($taxrule);
                            }
    
                            $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                        } else {
                            if ($ProductClass->getTaxRule()) {
                                $this->taxRuleRepository->delete($ProductClass->getTaxRule());
                                $ProductClass->setTaxRule(null);
                            }
                        }
                    }

                    $this->entityManager->persist($ProductClass);
                    $Product->setName($item['name']);
                    $Product->setDescriptionDetail($item['description_detail']);
                    if (array_key_exists('food_type', $item))
                        $Product->setFoodType($item['food_type']);
                    // $Product->setOrderableDate($item['orderable_date']);
                    $Product->setVisiblePrice($item['visible_price']);
                    $Product->setUpperLimit($item['upper_limit']);

                    $all_dressing_length = count($this->getDoctrine()->getRepository(Tag::class)->findAll());
                    $dressings = explode(',', $item['dressing']);

                    if (count($Product->getProductTag()) <= count($dressings)) {
                        $i = 0;
                        foreach($Product->getProductTag() as $ProductTag) {
                            $ProductTag->getTag()->setName($dressings[$i++]);
                            $this->entityManager->persist($ProductTag);
                        }
                        for(; $i < count($dressings); $i++) {
                            $Tag = new Tag();
                            $Tag->setName($dressings[$i]);
                            $Tag->setSortNo($all_dressing_length + $i);
    
                            $ProductTag = new ProductTag();

                            $ProductTag
                                ->setProduct($Product)
                                ->setTag($Tag);
    
                            $Product->addProductTag($ProductTag);
    
                            $this->entityManager->persist($Tag);
                            $this->entityManager->persist($ProductTag);
                        }
                    } else {
                        $i = 0;
                        foreach($Product->getProductTag() as $ProductTag) {
                            if ($i < count($dressings)) {
                                $ProductTag->getTag()->setName($dressings[$i++]);
                                $this->entityManager->persist($ProductTag);
                            } else {
                                $Product->removeProductTag($ProductTag);
                                $this->entityManager->remove($ProductTag->getTag());
                                $this->entityManager->remove($ProductTag);
                            }
                        }
                    }        
                    try {
                        $this->entityManager->flush();
                    } catch (\Exception $e) {
                        
                    }

                    // foreach($dressings as $dressing) {
                    //     $Tag = new Tag();
                    //     $Tag->setName($dressing);
                    //     $Tag->setSortNo($all_dressing_length + $dressing_index++);

                    //     $ProductTag = new ProductTag();
                    //     $ProductTag
                    //         ->setProduct($Product)
                    //         ->setTag($Tag);

                    //     $Product->addProductTag($ProductTag);

                    //     $this->entityManager->persist($Tag);
                    //     $this->entityManager->persist($ProductTag);
                    // }
                    
                    try {
                        $Product->setIsVisible($item['is_visible']);
                    } catch(\Exception $ex) {
                        echo $ex->getMessage();
                        $Product->setIsVisible(0);
                    }

                    $add_product_image = $item['food_image'];
                    $Product->setFoodImage($add_product_image);
    
                    if (count($Product->getProductImage()) == 0) {
                        if ($add_product_image != null) {
                            $ProductImage = new \Eccube\Entity\ProductImage();
                            $ProductImage
                                ->setFileName($add_product_image)
                                ->setProduct($Product)
                                ->setSortNo(1);
                            $Product->addProductImage($ProductImage);
                            $this->entityManager->persist($ProductImage);
        
                            // 移動
                            if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image)) {
                                $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image);
                                $file->move($this->eccubeConfig['eccube_save_image_dir']);
                            }
                        }
                    } else {
                        if ($Product->getProductImage()[0]->getFileName() != $add_product_image) {

                            foreach($Product->getProductImage() as $delete_item) {
                                $delete_image = $delete_item->getFileName();
                                $ProductImage = $this->productImageRepository->findOneBy(['file_name' => $delete_image]);
        
                                // 追加してすぐに削除した画像は、Entityに追加されない
                                
                                if (get_class($ProductImage) == 'Eccube\Entity\ProductImage') {
                                // if ($ProductImage instanceof ProductImage) {
                                    $Product->removeProductImage($ProductImage);
                                    $this->entityManager->remove($ProductImage);
                                }    
                                $this->entityManager->persist($Product);

                                // 削除
                                $fs = new Filesystem();
                                $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
                            }

                            $this->entityManager->flush();

                            $ProductImage = new \Eccube\Entity\ProductImage();
                            $ProductImage
                                ->setFileName($add_product_image)
                                ->setProduct($Product)
                                ->setSortNo(1);
                            $Product->addProductImage($ProductImage);
                            $this->entityManager->persist($ProductImage);

                            if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image)) {
                                $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image);
                                $file->move($this->eccubeConfig['eccube_save_image_dir']);
                            }
                        }
                    }
    
                    $Product->setRestaurant($Restaurant);
                    $Restaurant->addProduct($Product);
    
                    $this->entityManager->persist($ProductClass);
                    $this->entityManager->persist($Product);
                }
                $this->entityManager->persist($Restaurant);
                $this->entityManager->flush();
            }
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_RESTAURANT_EDIT_COMPLETE, $event);

            $this->addSuccess('登録しました。', 'admin');
            return $this->redirectToRoute('restaurant_edit', ['id' => $Restaurant->getId()]);
        }

        // Get Tags
        $TagsList = $this->tagRepository->getList();

        // ツリー表示のため、ルートからのカテゴリを取得
        $TopCategories = $this->categoryRepository->getList(null);
        $ChoicedCategoryIds = array_map(function ($Category) {
            return $Category->getId();
        }, $form->get('Category')->getData());

        $TagsList = $this->tagRepository->getList();

        $Products = $Restaurant->getProducts();

        return [
            'Restaurant' => $Restaurant,
            'id' => $id,
            'form' => $form->createView(),
            'TopCategories' => $TopCategories,
            'Tags' => $Tags,
            'TagsList' => $TagsList,
            'ChoicedCategoryIds' => $ChoicedCategoryIds,
            'Products' => $Products
        ];        
    }

    /**
     * @Route("/%eccube_admin_route%/restaurant/image/add", name="admin_restaurant_image_add", methods={"POST"})
     */
    public function addImage(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException();
        }

        if (isset($request->files->get('config')['products'])) $images = array_values($request->files->get('config')['products'])[0];
        else $images = $request->files->get('config');

        $allowExtensions = ['gif', 'jpg', 'jpeg', 'png'];
        $files = [];
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    // 拡張子
                    $extension = $image->getClientOriginalExtension();
                    if (!in_array(strtolower($extension), $allowExtensions)) {
                        throw new UnsupportedMediaTypeHttpException();
                    }

                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($this->eccubeConfig['eccube_temp_image_dir'], $filename);
                    $files[] = $filename;
                }
            }
        }
        $event = new EventArgs(
            [
                'images' => $images,
                'files' => $files,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_PRODUCT_ADD_IMAGE_COMPLETE, $event);
        $files = $event->getArgument('files');

        return $this->json(['files' => $files], 200);
    }

    /**
     * RestaurantCategory作成
     *
     * @param Plugin\restaurant_food\Entity\Config $Restaurant
     * @param Plugin\restaurant_food\Entity\RestaurantCategory $Category
     * @param integer $count
     *
     * @return Plugin\restaurant_food\Entity\RestaurantCategory
     */
    private function createRestaurantCategory($Restaurant, $Category, $count)
    {
        $RestaurantCategory = new RestaurantCategory();
        $RestaurantCategory->setRestaurant($Restaurant);
        $RestaurantCategory->setRestaurantId($Restaurant->getId());
        $RestaurantCategory->setCategory($Category);
        $RestaurantCategory->setCategoryId($Category->getId());

        return $RestaurantCategory;
    }

    function getErrorMessages(\Symfony\Component\Form\Form $form) {
        $errors = array();
    
        foreach ($form->getErrors() as $key => $error) {
            if ($form->isRoot()) {
                $errors['#'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }
    
        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
    
        return $errors;
    }
    
    /**
     * @Route("/restaurant/page/{id}", name="restaurant_page")
     * @Template("restaurant_page.twig")
     */
    public function restaurant_page_edit(Request $request, $id = null)
    {
        $code = $request->getSession()->get('restaurant_owner');

        if (is_null($code) || !$this->store_owner_validate($code))
            return $this->redirectToRoute('store_login');
        
        if (is_null($id)) {
            $Restaurant = new Restaurant();
        } else {
            $Restaurant = $this->configRepository->find($id);
            if (!$Restaurant) {
                throw new NotFoundHttpException();
            }
        }

        $form = $this->createForm(ConfigType::class, $Restaurant);

        $Products = $Restaurant->getProducts();

        $form['products']->setData($Products);

        $images = [];
        $RestaurantImages = $Restaurant->getRestaurantImage();
        foreach ($RestaurantImages as $RestaurantImage) {
            $images[] = $RestaurantImage->getFileName();
        }            
        // $restaurant_image_count = count($images);
        // if (isset($images[$restaurant_image_count - 1]))
        //     $form['images']->setData(array($images[$restaurant_image_count - 1]));
        $form['images']->setData($images);

        $categories = [];
        $RestaurantCategories = $Restaurant->getRestaurantCategories();
        foreach ($RestaurantCategories as $RestaurantCategory) {
            $categories[] = $RestaurantCategory->getCategory();
        }
        $form['Category']->setData($categories);

        $Tags = $Restaurant->getRestaurantTag();
        $form['Tag']->setData($Tags);

        $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        if ($form->isSubmitted()) {
            // if (!$form->isValid()) { print_r($this->getErrorMessages($form)); exit; }
            if (!$form->isValid());
            
            $Restaurant = $form->getData();

            // カテゴリの登録
            // 一度クリア
            /* @var $Restaurant Plugin\restaurant_food\Entity\Restaurant */
            foreach ($Restaurant->getRestaurantCategories() as $RestaurantCategory) {
                $Restaurant->removeRestaurantCategory($RestaurantCategory);
                $this->entityManager->remove($RestaurantCategory);
            }

            $Restaurant->setState(3);
            $this->entityManager->persist($Restaurant);
            $this->entityManager->flush();

            $count = 1;
            $Categories = $form->get('Category')->getData();
            
            $categoriesIdList = [];
            foreach ($Categories as $Category) {
                foreach ($Category->getPath() as $ParentCategory) {
                    if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                        $RestaurantCategory = $this->createRestaurantCategory($Restaurant, $ParentCategory, $count);
                        $this->entityManager->persist($RestaurantCategory);
                        $count++;
                        /* @var $Restaurant \Eccube\Entity\Restaurant */
                        $Restaurant->addRestaurantCategory($RestaurantCategory);
                        $categoriesIdList[$ParentCategory->getId()] = true;
                    }
                }
                if (!isset($categoriesIdList[$Category->getId()])) {
                    $RestaurantCategory = $this->createRestaurantCategory($Restaurant, $Category, $count);
                    $this->entityManager->persist($RestaurantCategory);
                    $count++;
                    /* @var $Restaurant \Eccube\Entity\Restaurant */
                    $Restaurant->addRestaurantCategory($RestaurantCategory);
                    $categoriesIdList[$ParentCategory->getId()] = true;
                }
            }

            $RestaurantTags = $Restaurant->getRestaurantTag();
            foreach ($RestaurantTags as $RestaurantTag) {
                $Restaurant->removeRestaurantTag($RestaurantTag);
                $this->entityManager->remove($RestaurantTag);
            }

            // 商品タグの登録
            $Tags = $form->get('Tag')->getData();
            foreach ($Tags as $Tag) {
                $RestaurantTag = new RestaurantTag();
                $RestaurantTag
                    ->setRestaurant($Restaurant)
                    ->setTag($Tag);
                $Restaurant->addRestaurantTag($RestaurantTag);
                $this->entityManager->persist($RestaurantTag);
            }
            $this->entityManager->flush();
            $event = new EventArgs(
                [
                    'form' => $form,
                    'Restaurant' => $Restaurant,
                ],
                $request
            );
            
            // 画像の登録
            $add_images = $form->get('add_images')->getData();
            foreach ($add_images as $add_image) {
                $RestaurantImage = new \Eccube\Entity\ProductImage();
                $RestaurantImage
                    ->setFileName($add_image)
                    ->setRestaurant($Restaurant)
                    ->setSortNo(1);
                $Restaurant->addRestaurantImage($RestaurantImage);
                $this->entityManager->persist($RestaurantImage);

                // 移動
                if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image)) {
                    $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_image);
                    $file->move($this->eccubeConfig['eccube_save_image_dir']);
                }
            }

            // 画像の削除
            $delete_images = $form->get('delete_images')->getData();
            foreach ($delete_images as $delete_image) {
                $RestaurantImage = $this->productImageRepository
                    ->findOneBy(['file_name' => $delete_image]);

                // 追加してすぐに削除した画像は、Entityに追加されない
                if (get_class($RestaurantImage) == 'Eccube\Entity\ProductImage') {
                    $Restaurant->removeRestaurantImage($RestaurantImage);
                    $this->entityManager->remove($RestaurantImage);
                }
                $this->entityManager->persist($Restaurant);

                // 削除
                $fs = new Filesystem();
                $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
            }

            if (array_key_exists('products', $request->get('config'))) {
                $form_products = $request->get('config')['products'];
                $form_keys = array();
                foreach($form_products as $form_product) {
                    if (intval($form_product['id']) != 0) array_push($form_keys, intval($form_product['id']));
                }

                $Products = $Restaurant->getProducts();

                foreach($Products as $Product) {
                    if(in_array($Product->getId(), $form_keys)) continue;

                    $Product->setRestaurant(null);
                    $Restaurant->removeProduct($Product);

                    $this->entityManager->remove($Product);
                }

                try {
                    $this->entityManager->persist($Restaurant);
                    $this->entityManager->flush();
                } catch (\Exception $ex) {
                    log_warning('URLの形式が不正です。');
                    echo $ex->getMessage();
                }

                foreach ($form_products as $item) {
                    if ($item['id'] == 0) {
                        $Product = new Product();
                        $ProductClass = new ProductClass();
                        $ProductStatus = $this->productStatusRepository->find(ProductStatus::DISPLAY_SHOW);
                        $Product
                            ->addProductClass($ProductClass)
                            ->setStatus($ProductStatus);
        
                        $saleType = $this->saleTypeRepository->find(1);
                        $ProductClass
                            ->setVisible(true)
                            ->setStockUnlimited(true)
                            ->setProduct($Product)
                            ->setPrice02($item['visible_price'])
                            ->setPrice01($item['visible_price'])
                            ->setSaleType($saleType);
                            
                        $this->entityManager->persist($ProductClass);
        
                        $ProductStock = new ProductStock();
                        $ProductStock->setStock(null);
                        $ProductClass->setProductStock($ProductStock);
                        $ProductStock->setProductClass($ProductClass);

                        $this->entityManager->persist($ProductStock);
                    } else {
                        $Product = $this->productRepository->find($item['id']);
                        $ProductClass = $Product->getProductClasses()[0];
                    }
    
    
                    // 個別消費税
                    if ($this->BaseInfo->isOptionProductTaxRule()) {
                        if ($ProductClass->getTaxRate() !== null) {
                            if ($ProductClass->getTaxRule()) {
                                $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                            } else {
                                $taxrule = $this->taxRuleRepository->newTaxRule();
                                $taxrule->setTaxRate($ProductClass->getTaxRate());
                                $taxrule->setApplyDate(new \DateTime());
                                $taxrule->setProduct($Product);
                                $taxrule->setProductClass($ProductClass);
                                $ProductClass->setTaxRule($taxrule);
                            }
    
                            $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                        } else {
                            if ($ProductClass->getTaxRule()) {
                                $this->taxRuleRepository->delete($ProductClass->getTaxRule());
                                $ProductClass->setTaxRule(null);
                            }
                        }
                    }
                    $this->entityManager->persist($ProductClass);
                    $Product->setName($item['name']);
                    $Product->setDescriptionDetail($item['description_detail']);
                    $Product->setFoodType($item['food_type']);
                    // $Product->setOrderableDate($item['orderable_date']);
                    $Product->setVisiblePrice($item['visible_price']);
                    $Product->setUpperLimit($item['upper_limit']);

                    $all_dressing_length = count($this->getDoctrine()->getRepository(Tag::class)->findAll());
                    $dressings = explode(',', $item['dressing']);

                    if (count($Product->getProductTag()) <= count($dressings)) {
                        $i = 0;
                        foreach($Product->getProductTag() as $ProductTag) {
                            $ProductTag->getTag()->setName($dressings[$i++]);
                            $this->entityManager->persist($ProductTag);
                        }
                        for(; $i < count($dressings); $i++) {
                            $Tag = new Tag();
                            $Tag->setName($dressings[$i]);
                            $Tag->setSortNo($all_dressing_length + $i);
    
                            $ProductTag = new ProductTag();
                            $ProductTag
                                ->setProduct($Product)
                                ->setTag($Tag);
    
                            $Product->addProductTag($ProductTag);
    
                            $this->entityManager->persist($Tag);
                            $this->entityManager->persist($ProductTag);
                        }
                    } else {
                        $i = 0;
                        foreach($Product->getProductTag() as $ProductTag) {
                            if ($i < count($dressings)) {
                                $ProductTag->getTag()->setName($dressings[$i++]);
                                $this->entityManager->persist($ProductTag);
                            } else {
                                $Product->removeProductTag($ProductTag);
                                $this->entityManager->remove($ProductTag->getTag());
                                $this->entityManager->remove($ProductTag);
                            }
                        }
                    }
                    
                    try {
                        $Product->setIsVisible($item['is_visible']);
                    } catch(\Exception $ex) {
                        echo $ex->getMessage();
                        $Product->setIsVisible(0);
                    }

                    $add_product_image = $item['food_image'];
                    $Product->setFoodImage($add_product_image);
    
                    if (count($Product->getProductImage()) == 0) {
                        if ($add_product_image != null) {
                            $ProductImage = new \Eccube\Entity\ProductImage();
                            $ProductImage
                                ->setFileName($add_product_image)
                                ->setProduct($Product)
                                ->setSortNo(1);
                            $Product->addProductImage($ProductImage);
                            $this->entityManager->persist($ProductImage);
        
                            // 移動
                            if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image)) {
                                $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image);
                                $file->move($this->eccubeConfig['eccube_save_image_dir']);
                            }
                        }
                    } else {
                        if ($Product->getProductImage()[0]->getFileName() != $add_product_image) {
                            foreach($Product->getProductImage() as $delete_item) {
                                $delete_image = $delete_item->getFileName();
                                $ProductImage = $this->productImageRepository->findOneBy(['file_name' => $delete_image]);
        
                                // 追加してすぐに削除した画像は、Entityに追加されない
                                
                                if (get_class($ProductImage) == 'Eccube\Entity\ProductImage') {
                                // if ($ProductImage instanceof ProductImage) {
                                    $Product->removeProductImage($ProductImage);
                                    $this->entityManager->remove($ProductImage);
                                }    
                                $this->entityManager->persist($Product);

                                // 削除
                                $fs = new Filesystem();
                                $fs->remove($this->eccubeConfig['eccube_save_image_dir'].'/'.$delete_image);
                            }

                            $this->entityManager->flush();

                            $ProductImage = new \Eccube\Entity\ProductImage();
                            $ProductImage
                                ->setFileName($add_product_image)
                                ->setProduct($Product)
                                ->setSortNo(1);
                            $Product->addProductImage($ProductImage);
                            $this->entityManager->persist($ProductImage);
                            
                            if (file_exists($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image)) {
                                $file = new File($this->eccubeConfig['eccube_temp_image_dir'].'/'.$add_product_image);
                                $file->move($this->eccubeConfig['eccube_save_image_dir']);
                            }
                        }
                    }
    
                    $Product->setRestaurant($Restaurant);
                    $Restaurant->addProduct($Product);
    
                    $this->entityManager->persist($ProductClass);
                    $this->entityManager->persist($Product);
                }
                $this->entityManager->persist($Restaurant);
                $this->entityManager->flush();
            }
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_RESTAURANT_EDIT_COMPLETE, $event);

            $this->addSuccess('登録しました。', 'admin');
            return $this->redirectToRoute('restaurant_page', ['id' => $Restaurant->getId()]);
        }

        // Get Tags
        $TagsList = $this->tagRepository->getList();

        // ツリー表示のため、ルートからのカテゴリを取得
        $TopCategories = $this->categoryRepository->getList(null);
        $ChoicedCategoryIds = array_map(function ($Category) {
            return $Category->getId();
        }, $form->get('Category')->getData());

        $TagsList = $this->tagRepository->getList();

        $Products = $Restaurant->getProducts();

        return [
            'Restaurant' => $Restaurant,
            'Restaurant_id' => $id,
            'form' => $form->createView(),
            'TopCategories' => $TopCategories,
            'Tags' => $Tags,
            'TagsList' => $TagsList,
            'ChoicedCategoryIds' => $ChoicedCategoryIds,
            'Products' => $Products
        ];
    }
    
    /**
     * @Route("/restaurant/permit/code", name="permit_code")
     * @Template("/Restaurant/permit.twig")
     */
    public function permit(Request $request) 
    {
        if (null !== $request->get('permit_code')) {
            $Restaurant = $this->configRepository->findOneBy(['regist_code' => $request->get('permit_code')]);
            if($Restaurant != null)
                return $this->redirectToRoute('restaurant_homepage', array('id' => $Restaurant->getId()));
        }
        return [];
    }

    public function store_owner_validate($code) {
        $Restaurants = $this->configRepository->findAll();

        foreach($Restaurants as $Restaurant) {
            if (md5($Restaurant->getRegistCode()) == $code)
                return true;
        }
        return false;
    }

    /**
     * @Route("/store/logout", name="store_logout")
     */
    public function logout(Request $request)
    {
        $request->getSession()->set('restaurant_owner', null);
        
        return $this->redirectToRoute('store_login');
    }

    /**
     * @Route("/store/login", name="store_login")
     * @Template("/Restaurant/login.twig")
     */
    public function login(Request $request, AuthenticationUtils $utils)
    {
        if (null !== $request->get('login_email')) {
            $res = $this->configRepository->findOneBy(['email' => $request->get('login_email'), 'password' => $request->get('login_pass')]);
            if($res != null) {
                $request->getSession()->set('restaurant_owner', md5($res->getRegistCode()));

                return $this->redirectToRoute('restaurant_homepage', array('id' => $res->getId()));
            }
        }

        /* @var $form \Symfony\Component\Form\FormInterface */
        $builder = $this->formFactory
            ->createNamedBuilder('', CustomerLoginType::class);

        $builder->get('login_memory')->setData((bool) $request->getSession()->get('_security.login_memory'));

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $Customer = $this->getUser();
            if ($Customer instanceof Customer) {
                $builder->get('login_email')
                    ->setData($Customer->getEmail());
            }
        }

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_LOGIN_INITIALIZE, $event);

        $form = $builder->getForm();

        return [
            'error' => $utils->getLastAuthenticationError(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/restaurant/{id}/order", name="order_history")
     * @Template("/Restaurant/order_index.twig")
     */
    public function order_index(Request $request, $id = null, $page_no = null, Paginator $paginator)
    {
        $code = $request->getSession()->get('restaurant_owner');

        if (is_null($code) || !$this->store_owner_validate($code))
            return $this->redirectToRoute('store_login');

        $Original_Orders = $this->orderRepository->findBy([],['id' => 'DESC']);
        $Orders = $Original_Orders;
        $unset_keys = array();

        foreach($Orders as $key => $Order) {
            foreach($Order->getOrderItems() as $OrderItem) {
                if ($OrderItem->getProduct() == null) {
                    $Order->removeOrderItem($OrderItem);
                    continue;
                }
                if ($OrderItem->getProduct()->getRestaurant()->getId() != $id) {
                    $Order->removeOrderItem($OrderItem);
                }
            }
            if (count($Order->getOrderItems()) == 0)
                array_push($unset_keys, $key);
        }
        foreach($unset_keys as $key)
            unset($Orders[$key]);

        $pagination = $paginator->paginate(
            $Orders,
            $request->get('pageno', 1),
            20
        );

        return [
            'Restaurant_id' => $id,
            'pagination' => $pagination
        ];
    }

    /**
     * 受注登録/編集画面.
     *
     * @Route("/restaurant/{id}/order/{order_no}", requirements={"id" = "\d+"}, name="order_edit")
     * @Template("/Restaurant/order_edit.twig")
     */
    public function order_edit(Request $request, $id = null, $order_no = null, RouterInterface $router)
    {
        $code = $request->getSession()->get('restaurant_owner');

        if (is_null($code) || !$this->store_owner_validate($code))
            return $this->redirectToRoute('store_login');

        $TargetOrder = null;
        $OriginOrder = null;

        if (null === $order_no) {
            // 空のエンティティを作成.
            $TargetOrder = new Order();
            $TargetOrder->addShipping((new Shipping())->setOrder($TargetOrder));

            $preOrderId = $this->orderHelper->createPreOrderId();
            $TargetOrder->setPreOrderId($preOrderId);
        } else {
            $TargetOrder = $this->orderRepository->find($order_no);
            if (null === $TargetOrder) {
                throw new NotFoundHttpException();
            }
        }

        // 編集前の受注情報を保持
        $OriginOrder = clone $TargetOrder;
        $OriginItems = new ArrayCollection();
        foreach ($TargetOrder->getOrderItems() as $Item) {
            $OriginItems->add($Item);
        }

        $builder = $this->formFactory->createBuilder(OrderType::class, $TargetOrder);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        $form->handleRequest($request);
        $purchaseContext = new PurchaseContext($OriginOrder, $OriginOrder->getCustomer());

        if ($form->isSubmitted() && $form['OrderItems']->isValid()) {
            $event = new EventArgs(
                [
                    'builder' => $builder,
                    'OriginOrder' => $OriginOrder,
                    'TargetOrder' => $TargetOrder,
                    'PurchaseContext' => $purchaseContext,
                ],
                $request
            );
            $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_PROGRESS, $event);

            $flowResult = $this->purchaseFlow->validate($TargetOrder, $purchaseContext);

            if ($flowResult->hasWarning()) {
                foreach ($flowResult->getWarning() as $warning) {
                    $this->addWarning($warning->getMessage(), 'admin');
                }
            }

            if ($flowResult->hasError()) {
                foreach ($flowResult->getErrors() as $error) {
                    $this->addError($error->getMessage(), 'admin');
                }
            }

            // 登録ボタン押下
            switch ($request->get('mode')) {
                case 'register':
                    log_info('受注登録開始', [$TargetOrder->getId()]);

                    if (!$flowResult->hasError() && $form->isValid()) {
                        try {
                            $this->purchaseFlow->prepare($TargetOrder, $purchaseContext);
                            $this->purchaseFlow->commit($TargetOrder, $purchaseContext);
                        } catch (PurchaseException $e) {
                            $this->addError($e->getMessage(), 'admin');
                            break;
                        }

                        $OldStatus = $OriginOrder->getOrderStatus();
                        $NewStatus = $TargetOrder->getOrderStatus();

                        // ステータスが変更されている場合はステートマシンを実行.
                        if ($TargetOrder->getId() && $OldStatus->getId() != $NewStatus->getId()) {
                            // 発送済に変更された場合は, 発送日をセットする.
                            if ($NewStatus->getId() == OrderStatus::DELIVERED) {
                                $TargetOrder->getShippings()->map(function (Shipping $Shipping) {
                                    if (!$Shipping->isShipped()) {
                                        $Shipping->setShippingDate(new \DateTime());
                                    }
                                });
                            }
                            // ステートマシンでステータスは更新されるので, 古いステータスに戻す.
                            $TargetOrder->setOrderStatus($OldStatus);
                            try {
                                // FormTypeでステータスの遷移チェックは行っているのでapplyのみ実行.
                                $this->orderStateMachine->apply($TargetOrder, $NewStatus);
                            } catch (ShoppingException $e) {
                                $this->addError($e->getMessage(), 'admin');
                                break;
                            }
                        }

                        $this->entityManager->persist($TargetOrder);
                        $this->entityManager->flush();

                        foreach ($OriginItems as $Item) {
                            if ($TargetOrder->getOrderItems()->contains($Item) === false) {
                                $this->entityManager->remove($Item);
                            }
                        }
                        $this->entityManager->flush();

                        // 新規登録時はMySQL対応のためflushしてから採番
                        $this->orderNoProcessor->process($TargetOrder, $purchaseContext);
                        $this->entityManager->flush();

                        // 会員の場合、購入回数、購入金額などを更新
                        if ($Customer = $TargetOrder->getCustomer()) {
                            $this->orderRepository->updateOrderSummary($Customer);
                            $this->entityManager->flush($Customer);
                        }

                        $event = new EventArgs(
                            [
                                'form' => $form,
                                'OriginOrder' => $OriginOrder,
                                'TargetOrder' => $TargetOrder,
                                'Customer' => $Customer,
                            ],
                            $request
                        );
                        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE, $event);

                        $this->addSuccess('admin.common.save_complete', 'admin');

                        log_info('受注登録完了', [$TargetOrder->getId()]);

                        if ($returnLink = $form->get('return_link')->getData()) {
                            try {
                                // $returnLinkはpathの形式で渡される. pathが存在するかをルータでチェックする.
                                $pattern = '/^'.preg_quote($request->getBasePath(), '/').'/';
                                $returnLink = preg_replace($pattern, '', $returnLink);
                                $result = $router->match($returnLink);
                                // パラメータのみ抽出
                                $params = array_filter($result, function ($key) {
                                    return 0 !== \strpos($key, '_');
                                }, ARRAY_FILTER_USE_KEY);

                                // pathからurlを再構築してリダイレクト.
                                return $this->redirectToRoute($result['_route'], $params);
                            } catch (\Exception $e) {
                                // マッチしない場合はログ出力してスキップ.
                                log_warning('URLの形式が不正です。');
                            }
                        }

                        return $this->redirectToRoute('admin_order_edit', ['id' => $TargetOrder->getId()]);
                    }

                    break;
                default:
                    break;
            }
        }

        // 会員検索フォーム
        $builder = $this->formFactory
            ->createBuilder(SearchCustomerType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_INITIALIZE, $event);

        $searchCustomerModalForm = $builder->getForm();

        // 商品検索フォーム
        $builder = $this->formFactory
            ->createBuilder(SearchProductType::class);

        $event = new EventArgs(
            [
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_INITIALIZE, $event);

        $searchProductModalForm = $builder->getForm();

        // 配送業者のお届け時間
        $times = [];
        $deliveries = $this->deliveryRepository->findAll();
        foreach ($deliveries as $Delivery) {
            $deliveryTimes = $Delivery->getDeliveryTimes();
            foreach ($deliveryTimes as $DeliveryTime) {
                $times[$Delivery->getId()][$DeliveryTime->getId()] = $DeliveryTime->getDeliveryTime();
            }
        }

        return [
            'form' => $form->createView(),
            'searchCustomerModalForm' => $searchCustomerModalForm->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Order' => $TargetOrder,
            'id' => $order_no,
            'Restaurant_id' => $id, 
            'shippingDeliveryTimes' => $this->serializer->serialize($times, 'json'),
        ];
    }

    /**
     * 受注CSVの出力.
     *
     * @Route("/order/export/csv", name="export_order_csv")
     *
     * @param Request $request
     *
     * @return StreamedResponse
     */
    public function exportOrder(Request $request)
    {
        $Restaurant_id = null;
        if (isset($_GET['id'])) $Restaurant_id = $_GET['id'];

        $filename = 'order_'.(new \DateTime())->format('YmdHis').'.csv';
        $response = $this->exportCsv($request, CsvType::CSV_TYPE_ORDER, $filename, $Restaurant_id);
        log_info('受注CSV出力ファイル名', [$filename]);

        return $response;
    }

    /**
     * @param Request $request
     * @param $csvTypeId
     * @param string $fileName
     *
     * @return StreamedResponse
     */
    protected function exportCsv(Request $request, $csvTypeId, $fileName, $Restaurant_id)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $this->entityManager;
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($request, $csvTypeId, $Restaurant_id) {
            // CSV種別を元に初期化.
            $this->csvExportService->initCsvType($csvTypeId);

            // ヘッダ行の出力.
            $this->csvExportService->exportHeader();

            // 受注データ検索用のクエリビルダを取得.
            $qb = $this->csvExportService
                ->getOrderQueryBuilder($request);

            // データ行の出力.
            $this->csvExportService->setExportQueryBuilder($qb);
            $this->csvExportService->exportData(function ($entity, $csvService) use ($request, $Restaurant_id) {
                $Csvs = $csvService->getCsvs();

                $Order = $entity;
                $OrderItems = $Order->getMergedProductOrderItems();

                if ($Restaurant_id == null || $Restaurant_id == $OrderItems[0]->getProduct()->getRestaurant()->getId()) {
                  foreach ($OrderItems as $OrderItem) {
                      $ExportCsvRow = new ExportCsvRow();
  
                      // CSV出力項目と合致するデータを取得.
                      foreach ($Csvs as $Csv) {
                          // 受注データを検索.
                          $ExportCsvRow->setData($csvService->getData($Csv, $Order));
                          if ($ExportCsvRow->isDataNull()) {
                              // 受注データにない場合は, 受注明細を検索.
                              $ExportCsvRow->setData($csvService->getData($Csv, $OrderItem));
                          }
                          if ($ExportCsvRow->isDataNull() && $Shipping = $OrderItem->getShipping()) {
                              // 受注明細データにない場合は, 出荷を検索.
                              $ExportCsvRow->setData($csvService->getData($Csv, $Shipping));
                          }
  
                          $event = new EventArgs(
                              [
                                  'csvService' => $csvService,
                                  'Csv' => $Csv,
                                  'OrderItem' => $OrderItem,
                                  'ExportCsvRow' => $ExportCsvRow,
                              ],
                              $request
                          );
                          $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_CSV_EXPORT_ORDER, $event);
  
                          $ExportCsvRow->pushData();
                      }
  
                      if ($OrderItem->isProduct()) {
                          if ( $csvService->getData($Csv, $OrderItem->getProduct()->getRestaurant()->getCompanyName(), 'restaurant_name') != null) {
                              $ExportCsvRow->setData($csvService->getData($Csv, $OrderItem->getProduct()->getRestaurant()->getCompanyName(), 'restaurant_name'));    
      
                              $event = new EventArgs(
                                  [
                                      'csvService' => $csvService,
                                      'Csv' => $Csv,
                                      'OrderItem' => $OrderItem,
                                      'ExportCsvRow' => $ExportCsvRow,
                                  ],
                                  $request
                              );
                              $this->eventDispatcher->dispatch(EccubeEvents::ADMIN_ORDER_CSV_EXPORT_ORDER, $event);
      
                              $ExportCsvRow->pushData();
                          }
                      }
                      $row[] = number_format(memory_get_usage(true));
                      // 出力.
                      $csvService->fputcsv($ExportCsvRow->getRow());
                  }
                }

            });
        });

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileName);
        $response->send();

        return $response;
    }
}
