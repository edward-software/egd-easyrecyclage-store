<?php

namespace Paprec\CommercialBundle\Form;

use Paprec\CommercialBundle\Entity\Agency;
use Paprec\CommercialBundle\Entity\BusinessLine;
use Paprec\CommercialBundle\Repository\AgencyRepository;
use Paprec\CommercialBundle\Repository\BusinessLineRepository;
use Paprec\UserBundle\Entity\User;
use Paprec\UserBundle\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderRequestEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('orderStatus', ChoiceType::class, array(
                "choices" => $options['status'],
            ))
            ->add('generatedTurnover', TextType::class)
            ->add('associatedOrder', FileType::class, array(
                'multiple' => false,
                'data_class' => null
            ))
            ->add('summary', TextareaType::class)
            ->add('tonnage', TextType::class)
            ->add('kookaburaNumber', TextType::class)
            ->add('userInCharge', EntityType::class, array(
                'class' => User::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data' => null,
                'choice_label' => 'username',
                'query_builder' => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.deleted IS NULL')
                        ->where('u.enabled = 1');
                }
            ))
            ->add('agency', EntityType::class, array(
                'class' => Agency::class,
                'multiple' => false,
                'expanded' => false,
                'placeholder' => '',
                'empty_data' => null,
                'choice_label' => 'name',
                'query_builder' => function (AgencyRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->where('a.deleted IS NULL');
                }
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paprec\CommercialBundle\Entity\OrderRequest',
            'status' => null
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
