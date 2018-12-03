<?php

namespace Paprec\CommercialBundle\Form\ProductD3EOrder;

use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Repository\BusinessLineRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3EOrderType extends AbstractType
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
                'choices' => array(
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
            ->add('orderStatus', ChoiceType::class, array(
                "choices" => $options['status'],
            ))
            ->add('totalAmount', TextType::class)
            ->add('associatedInvoice', FileType::class, array(
                'multiple' => false,
                'data_class' => null
            ))
            ->add('paymentMethod', ChoiceType::class, array(
                "choices" => $options['paymentMethods']
            ))
            ->add('installationDate', DateType::class, array(
                'widget' => 'single_text'
            ))
            ->add('removalDate', DateType::class, array(
                'widget' => 'single_text'
            ))
            ->add('domainType', ChoiceType::class, array(
                'choices' => array(
                    'Matériel présent sur le domaine privé' => 'private',
                    'Matériel présent sur le domain public' => 'public'
                )
            ))
            ->add('accessConditions', TextareaType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductD3EOrder',
            'status' => null,
            'paymentMethods' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_productd3eorder';
    }


}