<?php

namespace App\Security;

use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UtilisateurConfirmation implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if(!$user instanceof Utilisateur){
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if(!$user instanceof Utilisateur){
            return;
        }

        if(!$user->isIsVerified()){
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas vérifié, merci de le confirmer");
        }
    }
}