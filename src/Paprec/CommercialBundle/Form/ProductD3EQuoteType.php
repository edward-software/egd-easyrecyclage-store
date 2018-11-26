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

class ProductD3EQuoteType extends AbstractType
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
                'choice_label' => 'name',
                'query_builder' => function (BusinessLineRepository $er) {
                    return $er->createQueryBuilder('b')
                        ->where('b.deleted IS NULL')
                        ->andWhere('b.division = \'D3E\'');
                }
            ))
            ->add('civility', ChoiceType::class, array(
                'choices'  => array(
                    'M' => 'M',
                    'Mme' => 'Mme',
                ),
                'expanded' => true
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class)
            ->add('function', TextType::class, array(
                'required' => false
            ))
            ->add('email', TextType::class)
            ->add('address', TextareaType::class)
            ->add('postalCode', TextType::class)
            ->add('city', TextType::class)
            ->add('phone', TextType::class)
            ->add('quoteStatus', ChoiceType::class, array(
                "choices" => $options['status'],
            ))
            ->add('totalAmount', TextType::class)
            ->add('generatedTurnover', TextType::class)
            ->add('summary', TextareaType::class)
            ->add('frequency', ChoiceType::class, array(
                'choices'  => array(
                    'Régulier' => 'regular',
                    'Ponctuel' => 'ponctual',
                ),
                'expanded' => true
            ))
            ->add('tonnage', TextType::class)
            ->add('kookaburaNumber', TextType::class)
            ->add('userInCharge', EntityType::class, array(
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data'  => null,
                'choice_label' => 'username',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.deleted IS NULL')
                        ->andWhere('u.enabled = 1');
                }
            ))
            ->add('agency', EntityType::class, array(
                'class' => Agency::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data'  => null,
                'choice_label' => 'name',
                'query_builder' => function (AgencyRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL')
                        ->andWhere('a.divisions LIKE \'%D3E%\'');
                    ;
                }
            ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductD3EQuote',
            'status' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productD3Equote';
    }


}
