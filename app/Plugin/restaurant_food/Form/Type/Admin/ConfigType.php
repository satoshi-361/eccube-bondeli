<?php

namespace Plugin\restaurant_food\Form\Type\Admin;

use Plugin\restaurant_food\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Eccube\Entity\Category;
use Eccube\Repository\CategoryRepository;

use Eccube\Form\Type\AddressType;
use Eccube\Form\Type\PhoneNumberType;
use Eccube\Form\Type\PostalType;
use Eccube\Form\Type\Admin\ProductType;
use Eccube\Form\Validator\Email;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Eccube\Form\Type\RepeatedPasswordType;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * CustomerType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(CategoryRepository $categoryRepository, EccubeConfig $eccubeConfig)
    {
        $this->categoryRepository = $categoryRepository;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('name', TextType::class, [
            'required' => true,
        ])
        ->add('company_name', TextType::class, [
            'required' => true,
        ])
        ->add('restaurant_image', FileType::class, [
            'multiple' => true,
            'required' => false,
            'mapped' => false,
        ])
        ->add('postal_code', PostalType::class, [
            'required' => true,
        ])
        ->add('address', AddressType::class, [
            'required' => true,
        ])
        ->add('phone_number', PhoneNumberType::class, [
            'required' => true,
        ])
        ->add('email', EmailType::class, [
            'required' => true,
            'constraints' => [
                new Assert\NotBlank(),
                new Email(['strict' => $this->eccubeConfig['eccube_rfc_email_check']]),
            ],
            'attr' => [
                'placeholder' => 'common.mail_address_sample',
            ],
        ])
        ->add('password', RepeatedPasswordType::class)
        ->add('explanation', TextAreaType::class)
        ->add('is_deliverable_one', CheckboxType::class, [
                'required' => false,
                'label' => '配達可能時間の追記'
            ])
        ->add('deliverable', ChoiceType::class, [
            'choices' => [
                '即日' => 0,
                '1日後' => 1,
                '2日後' => 2,
                '3日後' => 3,
                '4日後' => 4,
                '5日後' => 5,
                '6日後' => 6,
                '7日後' => 7,
            ]
        ])
        ->add('deadline_start', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice'
        ])
        ->add('deadline_end', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice'
        ])
        ->add('deadline_start1', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice'
        ])
        ->add('deadline_end1', TimeType::class, [
            'input' => 'datetime',
            'widget' => 'choice'
        ])
        ->add('date_week', TextType::class, [])
        // ->add('date_week', ChoiceType::class, [
        //     'required' => true,
        //     'expanded' => true,
        //     'multiple' => true,
        //     'choices' => [
        //         '月' => 0,
        //         '火' => 1,
        //         '水' => 2,
        //         '木' => 3,
        //         '金' => 4,
        //         '土' => 5,
        //         '日' => 6,
        //         '祝' => 7
        //     ]
        // ])
        // ->add('bank_account', TextType::class)
        // ->add('sales_amount', NumberType::class)
        // ->add('back_rate', NumberType::class)
        // 画像
        ->add('images', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'prototype' => true,
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ])
        ->add('add_images', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'prototype' => true,
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ])
        ->add('delete_images', CollectionType::class, [
            'entry_type' => HiddenType::class,
            'prototype' => true,
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
        ])
        //products
        ->add('products', CollectionType::class, [
            'required' => false,
            'entry_type' => ProductType::class,
            'prototype' => true,
            'mapped' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'by_reference' => false,
        ])
        ->add('Category', ChoiceType::class, [
            'choice_label' => 'Name',
            'multiple' => true,
            'mapped' => false,
            'expanded' => true,
            'choices' => $this->categoryRepository->getList(null, true),
            'choice_value' => function (Category $Category = null) {
                return $Category ? $Category->getId() : null;
            },
        ])

        // 詳細な説明
        ->add('Tag', EntityType::class, [
            'class' => 'Plugin\restaurant_food\Entity\RestaurantTag',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'mapped' => false,
        ])
        ->add('delivery_fees', TextType::class, [
            'required' => true,
        ])
        ->add('deliverable_area', TextType::class, [
            'required' => true,
        ])
        ->add('lower_price_limit', NumberType::class, [
            'required' => true,
        ])
        ->add('food_type_list', TextType::class, [
            'required' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
