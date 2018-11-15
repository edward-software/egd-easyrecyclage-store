<?php

namespace Paprec\CommercialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDIOrderType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dateCreation')->add('dateUpdate')->add('deleted')->add('businessName')->add('civility')->add('lastName')->add('firstName')->add('email')->add('address')->add('postalCode')->add('city')->add('phone')->add('offerStatus')->add('totalAmount')->add('generatedTurnover')->add('summary')->add('frequency')->add('tonnage')->add('kookaburaNumber')->add('userInCharge')->add('agency');
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
