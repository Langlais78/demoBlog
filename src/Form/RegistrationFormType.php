<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($options['userRegistration'] == true)
        {
            $builder
                ->add('email', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('prenom', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('nom', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('adresse', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('ville', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('codePostal', TextType::class, [
                    'required' => false,
                    'constraints' => [
                        new NotBlank([
                            'message' => "Veuillez renseigner votre email"
                        ])
                    ]
                ])
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'required' => false,
                    'invalid_message' => "les mots de passe ne correspondent pas",
                    'options' => [
                        'attr' => [
                            'class' => 'password-field'
                        ]
                    ],
                    'first_options' => [
                        'label' => 'mot de passe'
                    ],
                    'second_options' => [
                        'label' => "Confirmer votre mot de passe"
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez renseigner votre mot de passe'
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => "Votre mot de passe doit contenir au minimum 8 caractères"
                        ])
                    ]
                ])
            ;
        }
        elseif($options['userUpdate'] == true)
        {
            $builder
            ->add('email', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ])
            ->add('prenom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ])
            ->add('nom', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ])
            ->add('adresse', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ])
            ->add('ville', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ])
            ->add('codePostal', TextType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez renseigner votre email"
                    ])
                ]
            ]);
        }
        elseif($options['userBack'] == true)
        {
            $builder
            ->add('roles', ChoiceType::class, [
                    'choices' =>[
                        'Utilisateur' => '',
                        'Administrateur' => 'ROLE_ADMIN'
                        ],
                        'expanded' => false,
                        'multiple' => true,
                        'label' => "Definir le role de l'utilisateur : ",
                        'attr' => [
                            'class' => 'select-admin'
                        ],
            ]);
           
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'userRegistration' => false,
            'userUpdate' => false,
            'userBack' => false
        ]);
    }
}
