<?php

namespace Paprec\CatalogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductD3EType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class)
            ->add('reference')
            ->add('coefHandling')
            ->add('coefSerialNumberStmt')
            ->add('coefDestruction')
            ->add('position')
            ->add('availablePostalCodes', TextareaType::class)
            ->add('isDisplayed', ChoiceType::class, array(
                "choices" => array(
                    'Non' => 0,
                    'Oui' => 1
                ),
                "expanded" => true
            ))
            ->add('isPayableOnline', ChoiceType::class, array(
                "choices" => array(
                    'Non' => 0,
                    'Oui' => 1
                ),
                "expanded" => true
            ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductD3E'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_productd3e';
    }


}
