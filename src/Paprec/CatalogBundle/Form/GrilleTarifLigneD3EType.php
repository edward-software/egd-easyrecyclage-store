<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Repository\AgencyRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('agency', EntityType::class, array(
                'class' => Agency::class,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'name',
                'query_builder' => function (AgencyRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL')
                        ->andWhere('a.divisions LIKE \'%D3E%\'');
                }
            ))
            ->add('minQuantity')
            ->add('maxQuantity', IntegerType::class, array(
                'required' => false
            ))
            ->add('price')
            ->add('save', SubmitType::class, array(
                'attr' => array('class' => 'save'),
                'label' => 'Catalog.GrilleTarifD3E.Save'
            ));
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
