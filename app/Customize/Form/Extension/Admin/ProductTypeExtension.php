<?php

namespace Customize\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('visible_price', NumberType::class)
        ->add('food_type', TextType::class)
        ->add('food_image', TextType::class)
        ->add('is_visible', CheckboxType::class, [
            'required' => true,
            'label' => '表示 / 非表示'
        ])
        ->add('dressing', TextType::class)
        ->add('upper_limit', NumberType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}