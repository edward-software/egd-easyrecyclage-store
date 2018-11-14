<?php

namespace Paprec\CommercialBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('divisions', ChoiceType::class, array(
                "choices" => $options['divisions'],
                "expanded" => true,
                "multiple" => true
            ))
            ->add('address')
            ->add('postalCode')
            ->add('city')
            ->add('phone')
            ->add('latitude')
            ->add('longitude')
            ->add('isDisplayed', ChoiceType::class, array(
                "choices" => array(
                    'Non' => 0,
                    'Oui' => 1
                ),
                "expanded" => true
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\Agency',
            'divisions' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_agence';
    }


}
