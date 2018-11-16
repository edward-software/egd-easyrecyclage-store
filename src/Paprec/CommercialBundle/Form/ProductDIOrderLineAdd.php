<?php

namespace Paprec\CommercialBundle\Form;

use Paprec\CatalogBundle\Repository\ProductDIRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductDIOrderLineAdd extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quantity', TextType::class, array(
                "required" => true
            ))
            ->add('productDI', EntityType::class, array(
                    'class' => 'PaprecCatalogBundle:ProductDI',
                    'query_builder' => function(ProductDIRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->where('p.deleted is NULL')
                            ->orderBy('p.name', 'ASC');
                    },
                    'choice_label' => 'name',
                )
            )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\ProductDIOrderLine'
        ));
    }
}
