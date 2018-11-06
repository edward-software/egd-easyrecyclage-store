<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CommercialBundle\Entity\Agence;
use Paprec\CommercialBundle\Repository\AgenceRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;

class GrilleTarifLigneD3EType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('postalCodes')
            ->add('agence', EntityType::class, array(
                'class' => Agence::class,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'name',
                'query_builder' => function (AgenceRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL')
                        ->where('a.divisions LIKE \'%D3E%\'');
                }
            ))
            ->add('minQuantity')
            ->add('maxQuantity')
            ->add('price');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\GrilleTarifLigneD3E'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_grilletarifligned3e';
    }


}
