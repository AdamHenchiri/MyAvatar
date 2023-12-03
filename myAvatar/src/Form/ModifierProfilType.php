<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class ModifierProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 20,
                    'minMessage' => 'Minimum 3 caractères',
                    'maxMessage' => 'Maximum 20 caractères',
                ]
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'minlength' => 3,
                    'maxlength' => 20,
                    'minMessage' => 'Minimum 3 caractères',
                    'maxMessage' => 'Maximum 20 caractères',
                ]
            ])
            ->add('email', EmailType::class)
            ->add('photoProfil', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File(maxSize : '10M', extensions : ['jpg', 'png', 'jpeg'])
                ],'attr' => [
                    'maxSizeMessage' => 'Fichier trop grand',
                    'extensionsMessage' => 'PNG, JPG ou JPEG',
                ]
            ])
            ->add('login', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'minMessage' => 'Minimum 4 caractères',
                    'maxMessage' => 'Maximum 20 caractères',
                ]
            ])
            ->add('oldPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'invalid_message' => 'mauvais mot de passe.',
            ])
            ->add('password', RepeatedType::class, [
                'mapped' => false,
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirmation mot de passe'],
                'constraints' => [
                    new Regex('#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$#', message: 'Your password must contain at least one capital letter, a small letter and one number'),
                    new Length([
                        'min' => 8,
                        'max' => 20,
                        'minMessage' => 'Mot de passe trop court (>8)',
                        'maxMessage' => 'Mot de passe trop long (<20)'
                    ])
                ],'attr'=>[
                    'minlength' => 8,
                    'maxlength' => 20,
                    'pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)[a-zA-Z\\d]{8,20}$',
                ]
            ])
            ->add('edit' , SubmitType::class, [
                'label' => 'Modifier'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}