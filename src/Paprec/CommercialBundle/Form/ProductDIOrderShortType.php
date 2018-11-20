<?php

namespace Paprec\CommercialBundle\Form;

use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Repository\AgencyRepository;
use Paprec\CommercialBundle\Repository\BusinessLineRepository;
use Paprec\UserBundle\Entity\User;
use Paprec\UserBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDIOrderShortType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('businessName')
            ->add('businessLine', EntityType::class, array(
                'class' => BusinessLine::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'Commercial.ProductDIOrder.BusinessLinePlaceholder',
                'empty_data'  => null,
                'choice_label' => 'name',
                'query_builder' => function (BusinessLineRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.deleted IS NULL');
                }
            ))
            ->add('civility', ChoiceType::class, array(
                'choices'  => array(
                    'Monsieur' => 'M',
                    'Madame' => 'Mme',
                ),
                'choice_attr' => function () {
                    return  ['class' => 'input__radio'];
                },
                'expanded' => true
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('email', TextType::class)
            ->add('address', TextareaType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('phone', TextType::class)
            ->add('function', TextType::class, array(
                'required' => false
            ));

    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductDIOrder'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productdiorder';
    }


}
