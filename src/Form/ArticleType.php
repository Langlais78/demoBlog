<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre de l\'article :',
                'required' => false,
                'attr' => [
                    'placeholder' => "Saisir le titre de l'article",
                ],
                'constraints' => [
                    new Length([
                        'min' => 10,
                        'max' => 50,
                        'minMessage' => "Titre trop court (min 10 caractères)",
                        'maxMessage' => "Titre trop long (max 50 caractères)"
                    ]),
                    New NotBlank([
                        'message' => "Merci de saisir un autre titre"
                    ])
                ]
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu de l\'article :',
                'required' => false,
                'attr' => [
                    'placeholder' =>"Saisir le contenue de l'article",
                    'rows' => 10
                ],
                'constraints' =>[
                New NotBlank([
                    'message' => "Merci de saisir un contenu d'article"
                ])
                ]
            ])
            ->add('photo', FileType::class, [
                'label' => 'Uploader une photo :',
                "mapped" => true, //
                'required' => false,
                "data_class" => null,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        "mimeTypesMessage" => 'Format autorisés : jpg/png/jpeg.'
                    ]),
                ]
                         
             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
