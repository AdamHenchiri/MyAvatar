<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'minMessage' => 'Minimum 4 caractères',
                    'maxMessage' => 'Maximum 20 caractères',
                ]
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'minlength' => 4,
                    'maxlength' => 20,
                    'minMessage' => 'Minimum 4 caractères',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
