<?php

namespace Customize\Form\Extension\Admin;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Admin\CustomerType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CustomerTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      $builder
        ->add('nickname', TextType::class, [
            'required' => false
        ])
        ->add('credit_card', TextType::class, [
            'required' => false
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getExtendedType(): string
    {
        return CustomerType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): iterable
    {
        return [CustomerType::class];
    }
}