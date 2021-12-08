<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('auteur', TextType::class, [
                'label' => 'Saisir votre nom et prenom :',
                'required' => false,
                'constraints' => [
                    New NotBlank([
                        'message' => "Merci de saisir un Nom"
                    ])
                ]
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Votre commentaire :',
                'required' => false,
                'attr' => [
                    'rows' => 10
                ],
                'constraints' => [
                    New NotBlank([
                        'message' => "Merci de saisir un Commentaire"
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
