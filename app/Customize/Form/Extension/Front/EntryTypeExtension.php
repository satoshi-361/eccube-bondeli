<?php

namespace Customize\Form\Extension\Front;

use Eccube\Common\EccubeConfig;
use Eccube\Form\Type\Front\EntryType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EntryTypeExtension extends AbstractTypeExtension
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
        return EntryType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): iterable
    {
        return [EntryType::class];
    }
}