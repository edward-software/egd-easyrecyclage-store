<?php

namespace Paprec\CatalogBundle\Form;

use Paprec\CatalogBundle\Entity\Argument;
use Paprec\CatalogBundle\Entity\Category;
use Paprec\CatalogBundle\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductChantierType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class)
            ->add('capacity')
            ->add('capacityUnit')
            ->add('dimensions', TextareaType::class)
            ->add('reference')
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
            ->add('unitPrice')
            ->add('availablePostalCodes')
            ->add('arguments', EntityType::class, array(
                'class' => Argument::class,
                'multiple' => true,
                'expanded' => true
            ))
            ->add('categories', EntityType::class, array(
                'class' => Category::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'name',
                'query_builder' => function (CategoryRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.deleted IS NULL');
                }
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CatalogBundle\Entity\ProductChantier'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_catalogbundle_productchantier';
    }



}
