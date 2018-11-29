<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Entity\PriceListD3E;
use Paprec\CatalogBundle\Repository\PriceListD3ERepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ))
            ->add('priceListD3E', EntityType::class, array(
                'class' => PriceListD3E::class,
                'multiple' => false,
                'expanded' => false,
                'choice_label' => 'name',
                'query_builder' => function (PriceListD3ERepository $er) {
                    return $er->createQueryBuilder('g')
                        ->where('g.deleted IS NULL');
                }
            ));
    }

    /**
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
