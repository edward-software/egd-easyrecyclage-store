<?php

namespace Paprec\CommercialBundle\Form\QuoteRequestNonCorporate;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteRequestNonCorporateShortType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('businessName')
            ->add('postalCode', HiddenType::class, array(
                'error_bubbling'=>false
            ))
            ->add('civility', ChoiceType::class, array(
                'choices' => array(
                    'Monsieur' => 'M',
                    'Madame' => 'Mme',
                ),
                'choice_attr' => function () {
                    return  ['class' => 'input__radio input__radio--short'];
                },
                'expanded' => true
            ))
            ->add('lastName', TextType::class)
            ->add('firstName', TextType::class, array(
                'required' => false
            ))
            ->add('email', TextType::class)
            ->add('phone', TextType::class)
            ->add('function', TextType::class)
            ->add('need', TextareaType::class, array(
                'attr' => array('cols' => '30', 'rows' => '10')
            ))
            ->add('attachedFiles', FileType::class, array(
                'multiple' => true,
                'data_class' => null
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\QuoteRequestNonCorporate'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'paprec_commercialbundle_quoteRequestNonCorporate';
    }


}
