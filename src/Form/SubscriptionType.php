<?php

namespace App\Form;

use App\Entity\Attendee;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Nom',
                    ],
                ]
            )
            ->add(
                'firstname',
                TextType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Prénom',
                    ],
                ]
            )
            ->add(
                'pseudo',
                TextType::class,
                [
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Pseudo sur le forum (optionnel)',
                    ],
                ]
            )
            ->add(
                'phoneNumber',
                TextType::class,
                [
                    'required' => true,
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'Numéro de téléphone',
                    ],
                ]
            )
            ->add(
                'email',
                RepeatedType::class,
                [
                    'required' => true,
                    'first_options' => [
                        'label' => false,
                        'attr' => [
                            'placeholder' => 'Email',
                        ],
                    ],
                    'second_options' => [
                        'label' => false,
                        'attr' => [
                            'placeholder' => 'Retaper l\'email',
                        ],
                    ],
                    'invalid_message' => 'Les emails ne sont pas identiques.',
                ]
            )
            ->add(
                'ticketNumber',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Nombre de tickets souhaités (tarif groupe)',
                    'attr' => [
                        'placeholder' => 0,
                    ],
                    'empty_data' => 0,
                ]
            )
            ->add(
                'seats',
                IntegerType::class,
                [
                    'required' => false,
                    'label' => 'Nombre de places disponibles',
                    'attr' => [
                        'placeholder' => 0,
                    ],
                ]
            )
            ->add(
                'originCity',
                TextType::class,
                [
                    'required' => false,
                    'label' => 'Je conduis une voiture depuis',
                    'attr' => [
                        'placeholder' => 'Ville',
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Inscription !',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => Attendee::class,
            )
        );
    }
}